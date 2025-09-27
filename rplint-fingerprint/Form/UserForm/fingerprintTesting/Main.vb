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
Imports System.Windows.Forms.VisualStyles.VisualStyleElement

Public Class Main
    Dim prevXml As String = ""
    Dim fullname As String
    Dim studentId As Integer
    Dim WithEvents timer As New Timers.Timer()

    Dim currentTime As DateTime
    Dim localFingerprintData As JArray = Nothing
    Delegate Sub SafeCallDelegate(ByVal text As String)

    Private Async Sub Form1_Load(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles MyBase.Load
        FingerPrintControll1.onLoad()
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
            Dim client As New HttpClient()
            Dim response As HttpResponseMessage = Await client.GetAsync("http://localhost:8000/api/students/fingerprint")
            Dim responseBody As String = Await response.Content.ReadAsStringAsync()

            If Not response.IsSuccessStatusCode Then
                MsgBox("Gagal ambil data fingerprint dari server pas load awal: " & responseBody)
                Return
            End If

            localFingerprintData = JArray.Parse(responseBody)
        Catch ex As Exception
            MsgBox("Error ambil fingerprint pas load awal: " & ex.Message)
        End Try
    End Function

    Private Sub Timer_Elapsed(sender As Object, e As ElapsedEventArgs)
        currentTime = currentTime.AddSeconds(1)
        If Me.InvokeRequired Then
            Me.Invoke(New Action(Of String)(AddressOf UpdateTimeDisplay), currentTime.ToString("HH:mm:ss"))
        Else
            UpdateTimeDisplay(currentTime.ToString("HH:mm:ss"))
        End If

        If Me.InvokeRequired Then
            Me.Invoke(New Action(AddressOf UpdateDateLabel))
        Else
            UpdateDateLabel()
        End If
    End Sub

    Private Sub UpdateDateLabel()
        todayLabel.Text = currentTime.ToString("dddd, dd MMMM yyyy", New System.Globalization.CultureInfo("id-ID"))
    End Sub

    Private Sub UpdateTimeDisplay(time As String)
        timeDisplay.Text = time
    End Sub

    Private Async Sub LoadDataGridView()
        Try
            Dim client As New HttpClient()
            Dim response As HttpResponseMessage = Await client.GetAsync("http://localhost:8000/api/students/attendances")
            Dim responseBody As String = Await response.Content.ReadAsStringAsync()

            If Not response.IsSuccessStatusCode Then
                MsgBox("Gagal ambil data dari server: " & responseBody)
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
            MsgBox("Error: " & ex.Message)
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
            MsgBox("Gagal memutar suara: " & ex.Message)
        End Try
    End Sub


    Public Class AutoCloseMessageBox
        Private Shared timeout As Integer
        Private Shared msgBox As Form

        Public Shared Sub Show(message As String, title As String, timeoutMs As Integer)
            timeout = timeoutMs
            msgBox = New Form() With {
                .Text = title,
                .Size = New Size(300, 150),
                .StartPosition = FormStartPosition.CenterScreen,
                .FormBorderStyle = FormBorderStyle.FixedDialog,
                .ControlBox = False,
                .ShowInTaskbar = False,
                .TopMost = True
            }

            Dim lblMessage As New Label() With {
                .Text = message,
                .Dock = DockStyle.Fill,
                .TextAlign = ContentAlignment.MiddleCenter,
                .Font = New Font("Segoe UI", 12, FontStyle.Regular)
            }

            msgBox.Controls.Add(lblMessage)

            Dim t As New Thread(Sub()
                                    Thread.Sleep(timeout)
                                    If msgBox.InvokeRequired Then
                                        msgBox.Invoke(Sub()
                                                          If msgBox.Visible Then msgBox.Close()
                                                      End Sub)
                                    Else
                                        If msgBox.Visible Then msgBox.Close()
                                    End If
                                End Sub)

            t.Start()

            If Application.OpenForms.Count > 0 Then
                Dim parentForm As Form = Application.OpenForms(0)
                parentForm.Invoke(Sub()
                                      msgBox.ShowDialog(parentForm)
                                  End Sub)
            Else
                msgBox.ShowDialog()
            End If

        End Sub
    End Class

    Private Async Sub ProcessAttendance(ByVal fpXml As String)
        Try
            Dim matchedStudentId As Integer = 0
            Dim matchedFullName As String = ""

            If localFingerprintData Is Nothing OrElse localFingerprintData.Count = 0 Then
                PlaySound("Resources/error.wav")
                AutoCloseMessageBox.Show("Data fingerprint lokal kosong, ulangi load data!", "Notification", 2000)
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
                    If Not String.IsNullOrWhiteSpace(template) AndAlso Not String.IsNullOrWhiteSpace(fpXml) Then
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
                AutoCloseMessageBox.Show("Fingerprint tidak ada di Database", "Notification", 2000)
                Return
            End If

            Dim payload = New With {
                .student_id = matchedStudentId,
                .scanned_at = DateTime.Now.ToString("yyyy-MM-dd HH:mm:ss")
            }

            Dim client As New HttpClient()
            Dim postContent = New StringContent(JsonConvert.SerializeObject(payload), Encoding.UTF8, "application/json")
            Dim postResponse As HttpResponseMessage = Await client.PostAsync("http://localhost:8000/api/students/fingerprint", postContent)
            Dim postBody As String = Await postResponse.Content.ReadAsStringAsync()

            If postResponse.IsSuccessStatusCode Then
                PlaySound("Resources/success.wav")
                AutoCloseMessageBox.Show("Absensi berhasil, " & matchedFullName, "Notification", 2000)
            Else
                PlaySound("Resources/error.wav")
                Dim errorMsg As String = JObject.Parse(postBody)("message").ToString()
                AutoCloseMessageBox.Show("Gagal absen: " & errorMsg, "Notification", 2000)
            End If

        Catch ex As Exception
            PlaySound("Resources/error.wav")
            AutoCloseMessageBox.Show("Error bang: " & ex.Message, "Notification", 2000)
        Finally
            If Me.InvokeRequired Then
                Me.Invoke(New Action(AddressOf LoadDataGridView))
            Else
                LoadDataGridView()
            End If
        End Try
    End Sub

    Private Sub WriteTextSafe(ByVal text As String)
        If Me.InvokeRequired Then
            Me.Invoke(New Action(Of String)(AddressOf WriteTextSafe), text)
        Else
            fullNameTxt.Text = text
        End If
    End Sub

    Private Sub SetText()
        WriteTextSafe(fullname)
    End Sub

    Private Sub timeDisplay_Click(sender As Object, e As EventArgs) Handles timeDisplay.Click, todayLabel.Click
    End Sub
End Class
