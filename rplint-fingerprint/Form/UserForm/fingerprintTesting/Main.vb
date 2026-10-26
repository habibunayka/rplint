Imports MySql.Data.MySqlClient
Imports Newtonsoft.Json
Imports Newtonsoft.Json.Linq
Imports System.IO
Imports System.Media
Imports System.Net.Http
Imports System.Text
Imports System.Threading
Imports System.Threading.Tasks
Imports System.Timers

Public Class Main
    Dim prevXml As String = ""
    Dim fullname As String
    Dim studentId As Integer
    Dim WithEvents timer As New Timers.Timer()

    Dim currentTime As DateTime
    Dim localFingerprintData As JArray = Nothing

    Dim authToken As String = "cVqzJR8VZtTFZmF3Ko4WPevhdTrVbwMAcMSpdQABI3"

    Dim client As New HttpClient()

    Delegate Sub SafeCallDelegate(ByVal text As String)

    Private Async Sub Form1_Load(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles MyBase.Load
        FingerPrintControll1.onLoad()

        client.DefaultRequestHeaders.Clear()
        client.DefaultRequestHeaders.Add("Authorization", "Bearer " & authToken)

        Await LoadFingerprintData()
        LoadDataGridView()

        timer.Interval = 1000
        AddHandler timer.Elapsed, AddressOf Timer_Elapsed
        timer.Start()

        currentTime = DateTime.Now
        UpdateDateLabel()
    End Sub

    Private Async Function LoadFingerprintData() As Task
        Try
            Dim response As HttpResponseMessage = Await client.GetAsync("https://rplint.cibionerpl.id/api/students/fingerprint")
            Dim responseBody As String = Await response.Content.ReadAsStringAsync()

            If Not response.IsSuccessStatusCode Then
                MsgBox("Gagal ambil data fingerprint: " & responseBody)
                Return
            End If

            localFingerprintData = JArray.Parse(responseBody)
        Catch ex As Exception
            MsgBox("Error ambil fingerprint: " & ex.Message)
        End Try
    End Function

    Private Sub Timer_Elapsed(sender As Object, e As ElapsedEventArgs)
        currentTime = currentTime.AddSeconds(1)

        If Me.InvokeRequired Then
            Me.Invoke(Sub() UpdateTimeDisplay(currentTime.ToString("HH:mm:ss")))
            Me.Invoke(Sub() UpdateDateLabel())
        Else
            UpdateTimeDisplay(currentTime.ToString("HH:mm:ss"))
            UpdateDateLabel()
        End If
    End Sub

    Private Sub UpdateDateLabel()
        todayLabel.Text = currentTime.ToString("dddd, dd MMMM yyyy", New Globalization.CultureInfo("id-ID"))
    End Sub

    Private Sub UpdateTimeDisplay(time As String)
        timeDisplay.Text = time
    End Sub

    Private Async Sub LoadDataGridView()
        Try
            Dim response As HttpResponseMessage = Await client.GetAsync("https://rplint.cibionerpl.id/api/students/attendances")
            Dim responseBody As String = Await response.Content.ReadAsStringAsync()

            If Not response.IsSuccessStatusCode Then
                MsgBox("Gagal ambil data tabel: " & responseBody)
                Return
            End If

            Dim dataArray As JArray = JArray.Parse(responseBody)
            Dim table As New DataTable()

            table.Columns.Add("Nama Siswa", GetType(String))
            table.Columns.Add("Kelas", GetType(String))
            table.Columns.Add("Status", GetType(String))
            table.Columns.Add("Waktu Masuk", GetType(String))
            table.Columns.Add("Waktu Keluar", GetType(String))

            For Each item As JObject In dataArray
                table.Rows.Add(
                    item("full_name").ToString(),
                    item("class_name").ToString(),
                    item("status").ToString(),
                    item("check_in_time").ToString(),
                    item("check_out_time").ToString()
                )
            Next

            DataGridView1.DataSource = table

        Catch ex As Exception
            MsgBox("Error load tabel: " & ex.Message)
        End Try
    End Sub

    Private Sub fingerPrintDetected(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles FingerPrintControll1.SomeEventEventArghs
        prevXml = sender.xmlValue
        ProcessAttendance(prevXml)
    End Sub

    Private Sub PlaySound(ByVal relativePath As String)
        Try
            Dim filePath As String = Path.Combine(Application.StartupPath, relativePath)
            Dim player As New SoundPlayer(filePath)
            player.Play()
        Catch ex As Exception
            MsgBox("Gagal memutar audio: " & ex.Message)
        End Try
    End Sub

    Private Async Sub ProcessAttendance(ByVal fpXml As String)
        Try
            Dim matchedStudentId As Integer = 0
            Dim matchedFullName As String = ""

            If localFingerprintData Is Nothing OrElse localFingerprintData.Count = 0 Then
                PlaySound("Resources/error.wav")
                AutoCloseMessageBox.Display("Data fingerprint kosong.", "Notification", 2000)
                Return
            End If

            For Each student As JObject In localFingerprintData

                Dim id As Integer = student("id")
                Dim fullName As String = student("full_name")

                Dim templates As New List(Of String) From {
                    student("fingerprint_template1").ToString(),
                    student("fingerprint_template2").ToString(),
                    student("fingerprint_template3").ToString()
                }

                For Each template As String In templates
                    If Not String.IsNullOrWhiteSpace(template) Then
                        Try
                            If FingerPrintControll1.compareFingerPrint(template, fpXml) Then
                                matchedStudentId = id
                                matchedFullName = fullName
                                Exit For
                            End If
                        Catch ex As Exception
                            If Not ex.Message.Contains("DP_INVALID_PARAMETER") Then
                                Throw
                            End If
                        End Try
                    End If
                Next

                If matchedStudentId <> 0 Then Exit For
            Next

            If matchedStudentId = 0 Then
                PlaySound("Resources/error.wav")
                AutoCloseMessageBox.Display("Sidik jari tidak dikenal.", "Notification", 2000)
                Return
            End If

            Dim payload = New With {
                .student_id = matchedStudentId,
                .scanned_at = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss")
            }

            Dim postContent = New StringContent(JsonConvert.SerializeObject(payload), Encoding.UTF8, "application/json")
            Dim postResponse As HttpResponseMessage = Await client.PostAsync("https://rplint.cibionerpl.id/api/students/fingerprint", postContent)

            Dim postBody As String = Await postResponse.Content.ReadAsStringAsync()

            If postResponse.IsSuccessStatusCode Then
                PlaySound("Resources/success.wav")
                AutoCloseMessageBox.Display("Absen berhasil: " & matchedFullName, "Notification", 2000)
            Else
                PlaySound("Resources/error.wav")

                Dim errObj As JObject = JObject.Parse(postBody)
                Dim errMsg As String = errObj("message").ToString()

                AutoCloseMessageBox.Display("Gagal absen: " & errMsg, "Notification", 2000)
            End If

        Catch ex As Exception
            PlaySound("Resources/error.wav")
            AutoCloseMessageBox.Display("Error bre: " & ex.Message, "Notification", 2000)
        Finally
            If Me.InvokeRequired Then
                Me.Invoke(Sub() LoadDataGridView())
            Else
                LoadDataGridView()
            End If
        End Try
    End Sub

    Public Class AutoCloseMessageBox
        Inherits Form

        Private closeTimer As New System.Windows.Forms.Timer()

        Private Sub New(message As String, title As String, timeoutMs As Integer)
            Me.Text = title
            Me.Size = New Size(300, 150)
            Me.StartPosition = FormStartPosition.CenterScreen
            Me.FormBorderStyle = FormBorderStyle.FixedDialog
            Me.ControlBox = False
            Me.ShowIcon = False
            Me.ShowInTaskbar = False
            Me.TopMost = True

            Dim lbl As New Label() With {
                .Text = message,
                .Dock = DockStyle.Fill,
                .TextAlign = ContentAlignment.MiddleCenter,
                .Font = New Font("Segoe UI", 12, FontStyle.Regular)
            }

            Me.Controls.Add(lbl)

            closeTimer.Interval = timeoutMs
            AddHandler closeTimer.Tick,
                Sub()
                    closeTimer.Stop()
                    Me.Close()
                    Me.Dispose()
                End Sub
        End Sub

        Public Shared Sub Display(message As String, title As String, timeoutMs As Integer)
            Dim showAction As Action =
                Sub()
                    Dim box As New AutoCloseMessageBox(message, title, timeoutMs)
                    box.Show()
                    box.TopMost = True
                    box.BringToFront()
                    box.Activate()
                    box.closeTimer.Start()
                End Sub

            If Application.OpenForms.Count > 0 Then
                Dim mainForm = Application.OpenForms(0)
                If mainForm.InvokeRequired Then
                    mainForm.Invoke(showAction)
                Else
                    showAction()
                End If
            Else
                showAction()
            End If
        End Sub
    End Class
End Class
