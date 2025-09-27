<Global.Microsoft.VisualBasic.CompilerServices.DesignerGenerated()> _
Partial Class Main
    Inherits System.Windows.Forms.Form

    'Form overrides dispose to clean up the component list.
    <System.Diagnostics.DebuggerNonUserCode()> _
    Protected Overrides Sub Dispose(ByVal disposing As Boolean)
        Try
            If disposing AndAlso components IsNot Nothing Then
                components.Dispose()
            End If
        Finally
            MyBase.Dispose(disposing)
        End Try
    End Sub

    'Required by the Windows Form Designer
    Private components As System.ComponentModel.IContainer

    'NOTE: The following procedure is required by the Windows Form Designer
    'It can be modified using the Windows Form Designer.  
    'Do not modify it using the code editor.
    <System.Diagnostics.DebuggerStepThrough()> _
    Private Sub InitializeComponent()
        Dim resources As System.ComponentModel.ComponentResourceManager = New System.ComponentModel.ComponentResourceManager(GetType(Main))
        Me.Label1 = New System.Windows.Forms.Label()
        Me.fullNameTxt = New System.Windows.Forms.TextBox()
        Me.updateBtn = New System.Windows.Forms.Button()
        Me.FingerPrintControll1 = New fingerPrintControl.fingerPrintControll()
        Me.DataGridView1 = New System.Windows.Forms.DataGridView()
        Me.idLabel = New System.Windows.Forms.Label()
        Me.id = New System.Windows.Forms.Label()
        Me.Label2 = New System.Windows.Forms.Label()
        Me.fingerprintTemplate = New System.Windows.Forms.TextBox()
        Me.selectClass = New System.Windows.Forms.ComboBox()
        Me.Label3 = New System.Windows.Forms.Label()
        Me.Label4 = New System.Windows.Forms.Label()
        Me.template2 = New System.Windows.Forms.RadioButton()
        Me.template3 = New System.Windows.Forms.RadioButton()
        Me.template1 = New System.Windows.Forms.RadioButton()
        CType(Me.DataGridView1, System.ComponentModel.ISupportInitialize).BeginInit()
        Me.SuspendLayout()
        '
        'Label1
        '
        Me.Label1.AutoSize = True
        Me.Label1.Location = New System.Drawing.Point(16, 136)
        Me.Label1.Margin = New System.Windows.Forms.Padding(4, 0, 4, 0)
        Me.Label1.Name = "Label1"
        Me.Label1.Size = New System.Drawing.Size(103, 16)
        Me.Label1.TabIndex = 1
        Me.Label1.Text = "Nama Lengkap:"
        '
        'fullNameTxt
        '
        Me.fullNameTxt.Location = New System.Drawing.Point(20, 156)
        Me.fullNameTxt.Margin = New System.Windows.Forms.Padding(4)
        Me.fullNameTxt.Name = "fullNameTxt"
        Me.fullNameTxt.ReadOnly = True
        Me.fullNameTxt.Size = New System.Drawing.Size(597, 22)
        Me.fullNameTxt.TabIndex = 2
        '
        'updateBtn
        '
        Me.updateBtn.Location = New System.Drawing.Point(517, 365)
        Me.updateBtn.Margin = New System.Windows.Forms.Padding(4)
        Me.updateBtn.Name = "updateBtn"
        Me.updateBtn.Size = New System.Drawing.Size(100, 28)
        Me.updateBtn.TabIndex = 3
        Me.updateBtn.Text = "Simpan"
        Me.updateBtn.UseVisualStyleBackColor = True
        '
        'FingerPrintControll1
        '
        Me.FingerPrintControll1.BackColor = System.Drawing.Color.White
        Me.FingerPrintControll1.CurrentReader = Nothing
        Me.FingerPrintControll1.Fmds = CType(resources.GetObject("FingerPrintControll1.Fmds"), System.Collections.Generic.Dictionary(Of Short, DPUruNet.Fmd))
        Me.FingerPrintControll1.Location = New System.Drawing.Point(653, 63)
        Me.FingerPrintControll1.Margin = New System.Windows.Forms.Padding(5)
        Me.FingerPrintControll1.Name = "FingerPrintControll1"
        Me.FingerPrintControll1.Reset = False
        Me.FingerPrintControll1.Size = New System.Drawing.Size(376, 330)
        Me.FingerPrintControll1.TabIndex = 0
        '
        'DataGridView1
        '
        Me.DataGridView1.ColumnHeadersHeightSizeMode = System.Windows.Forms.DataGridViewColumnHeadersHeightSizeMode.AutoSize
        Me.DataGridView1.Location = New System.Drawing.Point(19, 412)
        Me.DataGridView1.Name = "DataGridView1"
        Me.DataGridView1.RowHeadersWidth = 51
        Me.DataGridView1.RowTemplate.Height = 24
        Me.DataGridView1.Size = New System.Drawing.Size(1010, 320)
        Me.DataGridView1.TabIndex = 4
        '
        'idLabel
        '
        Me.idLabel.AutoSize = True
        Me.idLabel.Location = New System.Drawing.Point(21, 371)
        Me.idLabel.Margin = New System.Windows.Forms.Padding(4, 0, 4, 0)
        Me.idLabel.Name = "idLabel"
        Me.idLabel.Size = New System.Drawing.Size(77, 16)
        Me.idLabel.TabIndex = 1
        Me.idLabel.Text = "ID Terpilih : "
        '
        'id
        '
        Me.id.AutoSize = True
        Me.id.Location = New System.Drawing.Point(113, 371)
        Me.id.Margin = New System.Windows.Forms.Padding(4, 0, 4, 0)
        Me.id.Name = "id"
        Me.id.Size = New System.Drawing.Size(14, 16)
        Me.id.TabIndex = 1
        Me.id.Text = "0"
        '
        'Label2
        '
        Me.Label2.AutoSize = True
        Me.Label2.Location = New System.Drawing.Point(16, 190)
        Me.Label2.Margin = New System.Windows.Forms.Padding(4, 0, 4, 0)
        Me.Label2.Name = "Label2"
        Me.Label2.Size = New System.Drawing.Size(65, 16)
        Me.Label2.TabIndex = 1
        Me.Label2.Text = "Sidik Jari:"
        '
        'fingerprintTemplate
        '
        Me.fingerprintTemplate.Location = New System.Drawing.Point(20, 210)
        Me.fingerprintTemplate.Margin = New System.Windows.Forms.Padding(4)
        Me.fingerprintTemplate.Multiline = True
        Me.fingerprintTemplate.Name = "fingerprintTemplate"
        Me.fingerprintTemplate.ReadOnly = True
        Me.fingerprintTemplate.Size = New System.Drawing.Size(597, 95)
        Me.fingerprintTemplate.TabIndex = 2
        '
        'selectClass
        '
        Me.selectClass.DisplayMember = "All Class"
        Me.selectClass.DropDownStyle = System.Windows.Forms.ComboBoxStyle.DropDownList
        Me.selectClass.FormattingEnabled = True
        Me.selectClass.Items.AddRange(New Object() {"All Class", "10 RPL 1", "10 RPL 2", "11 RPL 1", "11 RPL 2", "12 RPL 1", "12 RPL 2"})
        Me.selectClass.Location = New System.Drawing.Point(351, 368)
        Me.selectClass.Name = "selectClass"
        Me.selectClass.Size = New System.Drawing.Size(147, 24)
        Me.selectClass.TabIndex = 5
        '
        'Label3
        '
        Me.Label3.AutoSize = True
        Me.Label3.Font = New System.Drawing.Font("Microsoft Sans Serif", 13.8!, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, CType(0, Byte))
        Me.Label3.Location = New System.Drawing.Point(17, 48)
        Me.Label3.Margin = New System.Windows.Forms.Padding(4, 0, 4, 0)
        Me.Label3.Name = "Label3"
        Me.Label3.Size = New System.Drawing.Size(481, 29)
        Me.Label3.TabIndex = 1
        Me.Label3.Text = "FINGERPRINT ATTENDANCE SYSTEM"
        '
        'Label4
        '
        Me.Label4.AutoSize = True
        Me.Label4.Font = New System.Drawing.Font("Microsoft Sans Serif", 10.8!, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, CType(0, Byte))
        Me.Label4.Location = New System.Drawing.Point(17, 86)
        Me.Label4.Margin = New System.Windows.Forms.Padding(4, 0, 4, 0)
        Me.Label4.Name = "Label4"
        Me.Label4.Size = New System.Drawing.Size(143, 22)
        Me.Label4.TabIndex = 1
        Me.Label4.Text = "By Kelompok 3"
        '
        'template2
        '
        Me.template2.AutoSize = True
        Me.template2.Location = New System.Drawing.Point(395, 329)
        Me.template2.Name = "template2"
        Me.template2.Size = New System.Drawing.Size(96, 20)
        Me.template2.TabIndex = 0
        Me.template2.TabStop = True
        Me.template2.Text = "Template 2"
        Me.template2.UseVisualStyleBackColor = True
        '
        'template3
        '
        Me.template3.AutoSize = True
        Me.template3.Location = New System.Drawing.Point(521, 329)
        Me.template3.Name = "template3"
        Me.template3.Size = New System.Drawing.Size(96, 20)
        Me.template3.TabIndex = 0
        Me.template3.TabStop = True
        Me.template3.Text = "Template 3"
        Me.template3.UseVisualStyleBackColor = True
        '
        'template1
        '
        Me.template1.AutoSize = True
        Me.template1.Location = New System.Drawing.Point(269, 329)
        Me.template1.Name = "template1"
        Me.template1.Size = New System.Drawing.Size(96, 20)
        Me.template1.TabIndex = 0
        Me.template1.TabStop = True
        Me.template1.Text = "Template 1"
        Me.template1.UseVisualStyleBackColor = True
        '
        'Main
        '
        Me.AutoScaleDimensions = New System.Drawing.SizeF(8.0!, 16.0!)
        Me.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font
        Me.ClientSize = New System.Drawing.Size(1057, 744)
        Me.Controls.Add(Me.template1)
        Me.Controls.Add(Me.template3)
        Me.Controls.Add(Me.template2)
        Me.Controls.Add(Me.FingerPrintControll1)
        Me.Controls.Add(Me.selectClass)
        Me.Controls.Add(Me.DataGridView1)
        Me.Controls.Add(Me.updateBtn)
        Me.Controls.Add(Me.fingerprintTemplate)
        Me.Controls.Add(Me.fullNameTxt)
        Me.Controls.Add(Me.id)
        Me.Controls.Add(Me.Label2)
        Me.Controls.Add(Me.idLabel)
        Me.Controls.Add(Me.Label4)
        Me.Controls.Add(Me.Label3)
        Me.Controls.Add(Me.Label1)
        Me.Margin = New System.Windows.Forms.Padding(4)
        Me.Name = "Main"
        Me.Text = "AdminForm"
        CType(Me.DataGridView1, System.ComponentModel.ISupportInitialize).EndInit()
        Me.ResumeLayout(False)
        Me.PerformLayout()

    End Sub
    Friend WithEvents FingerPrintControll1 As fingerPrintControl.fingerPrintControll
    Friend WithEvents Label1 As System.Windows.Forms.Label
    Friend WithEvents fullNameTxt As System.Windows.Forms.TextBox
    Friend WithEvents updateBtn As System.Windows.Forms.Button
    Friend WithEvents DataGridView1 As DataGridView
    Friend WithEvents idLabel As Label
    Friend WithEvents id As Label
    Friend WithEvents Label2 As Label
    Friend WithEvents fingerprintTemplate As TextBox
    Friend WithEvents selectClass As ComboBox
    Friend WithEvents Label3 As Label
    Friend WithEvents Label4 As Label
    Friend WithEvents template2 As RadioButton
    Friend WithEvents template3 As RadioButton
    Friend WithEvents template1 As RadioButton
End Class
