Imports System.IO
Imports System.Net
Imports System.Threading
Imports Newtonsoft.Json

Public Class Main
    Dim prevXml As String = ""
    Dim selectedId As Integer
    Dim fullname As String
    Dim authToken As String = "cVqzJR8VZtTFZmF3Ko4WPevhdTrVbwMAcMSpdQABI3"

    Delegate Sub SafeCallDelegate(ByVal text As String)

    Private Sub Form1_Load(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles MyBase.Load
        FingerPrintControll1.onLoad()
        Try
            LoadDataGridView()
        Catch ex As Exception
            MessageBox.Show("Something is error.", "Notification", MessageBoxButtons.OK, MessageBoxIcon.Error)
        End Try
    End Sub

    Private Sub AddAuthorizationHeader(ByRef request As HttpWebRequest)
        request.Headers.Add("Authorization", "Bearer " & authToken)
    End Sub

    Private Sub LoadDataGridView()
        Try
            Dim classSelected As String = selectClass.Text
            If classSelected.Equals("All Class", StringComparison.OrdinalIgnoreCase) Then
                classSelected = ""
            End If

            Dim url As String = $"https://rplint.cibionerpl.id/api/students?class={Uri.EscapeDataString(classSelected)}"
            Dim request As HttpWebRequest = WebRequest.Create(url)
            request.Method = "GET"
            AddAuthorizationHeader(request)

            Using response As HttpWebResponse = request.GetResponse()
                Using reader As New StreamReader(response.GetResponseStream())
                    Dim jsonResponse As String = reader.ReadToEnd()
                    Dim table As DataTable = JsonConvert.DeserializeObject(Of DataTable)(jsonResponse)
                    DataGridView1.DataSource = table
                End Using
            End Using
        Catch ex As Exception
            MsgBox("Error: " & ex.Message)
        End Try
    End Sub

    Private Sub fingerPrintDetected(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles FingerPrintControll1.SomeEventEventArghs
        prevXml = sender.xmlValue
        checkFingerPrintInDatabase(prevXml)
    End Sub

    Sub saveNewUser(ByVal fullname As String, ByVal fpXml As String)
        Try
            Dim fingerprintColumn As String = ""
            If template1.Checked Then
                fingerprintColumn = "fingerprint_template1"
            ElseIf template2.Checked Then
                fingerprintColumn = "fingerprint_template2"
            ElseIf template3.Checked Then
                fingerprintColumn = "fingerprint_template3"
            End If

            If String.IsNullOrEmpty(fingerprintColumn) Then
                Throw New Exception("No fingerprint template selected!")
            End If

            Dim url As String = $"https://rplint.cibionerpl.id/api/students/{selectedId}"
            Dim data = New With {
                Key .column = fingerprintColumn,
                Key .value = fpXml
            }

            Dim json As String = JsonConvert.SerializeObject(data)
            Dim request As HttpWebRequest = WebRequest.Create(url)
            request.Method = "PUT"
            request.ContentType = "application/json"
            AddAuthorizationHeader(request)

            Using streamWriter = New StreamWriter(request.GetRequestStream())
                streamWriter.Write(json)
            End Using

            Using response As HttpWebResponse = request.GetResponse()
                MessageBox.Show("Berhasil menyimpan sidik jari!", "Notification", MessageBoxButtons.OK, MessageBoxIcon.Information)
            End Using

            LoadDataGridView()
        Catch ex As Exception
            MsgBox("Error on Save: " & ex.Message)
        End Try
    End Sub

    Private Sub checkFingerPrintInDatabase(ByVal fpXml As String)
        Try
            Dim fingerprintValid As Boolean = FingerPrintControll1.compareFingerPrint(fpXml, fpXml)

            If fingerprintValid Then
                If fingerprintTemplate.InvokeRequired Then
                    fingerprintTemplate.Invoke(Sub() fingerprintTemplate.Text = fpXml)
                Else
                    fingerprintTemplate.Text = fpXml
                End If
            Else
                MsgBox("Fingerprint tidak valid.", MessageBoxIcon.Error)
            End If
        Catch ex As Exception
            MsgBox("Error: " & ex.Message)
        End Try
    End Sub

    Private Sub WriteTextSafe(ByVal text As String)
        If fullNameTxt.InvokeRequired Then
            Dim d As New SafeCallDelegate(AddressOf SetText)
            fullNameTxt.Invoke(d, New Object() {text})
        Else
            fullNameTxt.Text = text
        End If
    End Sub

    Private Sub SetText()
        WriteTextSafe(fullname)
    End Sub

    Private Sub saveBtn_Click(ByVal sender As System.Object, ByVal e As System.EventArgs) Handles updateBtn.Click
        Try
            saveNewUser(fullNameTxt.Text, prevXml)
        Catch ex As Exception
            MsgBox("Error: " & ex.Message)
        End Try
    End Sub

    Private Sub DataGridView1_MouseClick(sender As Object, e As MouseEventArgs) Handles DataGridView1.MouseClick
        UpdateSelectedRowData()
    End Sub

    Private Sub selectClass_SelectedIndexChanged(sender As Object, e As EventArgs) Handles selectClass.SelectedIndexChanged
        LoadDataGridView()
    End Sub

    Private Sub FingerPrintControll1_Load(sender As Object, e As EventArgs) Handles FingerPrintControll1.Load
    End Sub

    Private Sub template1_CheckedChanged(sender As Object, e As EventArgs) Handles template1.CheckedChanged
        UpdateSelectedRowData()
    End Sub

    Private Sub template2_CheckedChanged(sender As Object, e As EventArgs) Handles template2.CheckedChanged
        UpdateSelectedRowData()
    End Sub

    Private Sub template3_CheckedChanged(sender As Object, e As EventArgs) Handles template3.CheckedChanged
        UpdateSelectedRowData()
    End Sub

    Private Function GetCellValueSafe(row As DataGridViewRow, cellIndex As Integer) As String
        Try
            If IsDBNull(row.Cells(cellIndex).Value) OrElse row.Cells(cellIndex).Value Is Nothing Then
                Return ""
            Else
                Return row.Cells(cellIndex).Value.ToString()
            End If
        Catch ex As Exception
            Return ""
        End Try
    End Function


    Private Sub UpdateSelectedRowData()
        Try
            If DataGridView1.CurrentRow IsNot Nothing Then
                selectedId = Convert.ToInt32(DataGridView1.CurrentRow.Cells(0).Value)

                fullNameTxt.Text = GetCellValueSafe(DataGridView1.CurrentRow, 2)

                If template1.Checked Then
                    fingerprintTemplate.Text = GetCellValueSafe(DataGridView1.CurrentRow, 3)
                ElseIf template2.Checked Then
                    fingerprintTemplate.Text = GetCellValueSafe(DataGridView1.CurrentRow, 4)
                ElseIf template3.Checked Then
                    fingerprintTemplate.Text = GetCellValueSafe(DataGridView1.CurrentRow, 5)
                End If

                id.Text = selectedId.ToString()
            End If
        Catch ex As Exception
            MsgBox("Error: " & ex.Message)
        End Try
    End Sub

End Class