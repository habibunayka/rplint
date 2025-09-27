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
        Me.components = New System.ComponentModel.Container()
        Dim resources As System.ComponentModel.ComponentResourceManager = New System.ComponentModel.ComponentResourceManager(GetType(Main))
        Me.Label1 = New System.Windows.Forms.Label()
        Me.fullNameTxt = New System.Windows.Forms.TextBox()
        Me.FingerPrintControll1 = New fingerPrintControl.fingerPrintControll()
        Me.DataGridView1 = New System.Windows.Forms.DataGridView()
        Me.Label4 = New System.Windows.Forms.Label()
        Me.Label3 = New System.Windows.Forms.Label()
        Me.timeDisplay = New System.Windows.Forms.Label()
        Me.Timer1 = New System.Windows.Forms.Timer(Me.components)
        Me.todayLabel = New System.Windows.Forms.Label()
        CType(Me.DataGridView1, System.ComponentModel.ISupportInitialize).BeginInit()
        Me.SuspendLayout()
        '
        'Label1
        '
        Me.Label1.AutoSize = True
        Me.Label1.Location = New System.Drawing.Point(15, 276)
        Me.Label1.Margin = New System.Windows.Forms.Padding(4, 0, 4, 0)
        Me.Label1.Name = "Label1"
        Me.Label1.Size = New System.Drawing.Size(103, 16)
        Me.Label1.TabIndex = 1
        Me.Label1.Text = "Nama Lengkap:"
        '
        'fullNameTxt
        '
        Me.fullNameTxt.Location = New System.Drawing.Point(19, 296)
        Me.fullNameTxt.Margin = New System.Windows.Forms.Padding(4)
        Me.fullNameTxt.Name = "fullNameTxt"
        Me.fullNameTxt.ReadOnly = True
        Me.fullNameTxt.Size = New System.Drawing.Size(515, 22)
        Me.fullNameTxt.TabIndex = 2
        '
        'FingerPrintControll1
        '
        Me.FingerPrintControll1.BackColor = System.Drawing.Color.White
        Me.FingerPrintControll1.CurrentReader = Nothing
        Me.FingerPrintControll1.Fmds = CType(resources.GetObject("FingerPrintControll1.Fmds"), System.Collections.Generic.Dictionary(Of Short, DPUruNet.Fmd))
        Me.FingerPrintControll1.Location = New System.Drawing.Point(564, 28)
        Me.FingerPrintControll1.Margin = New System.Windows.Forms.Padding(5)
        Me.FingerPrintControll1.Name = "FingerPrintControll1"
        Me.FingerPrintControll1.Reset = False
        Me.FingerPrintControll1.Size = New System.Drawing.Size(399, 330)
        Me.FingerPrintControll1.TabIndex = 0
        '
        'DataGridView1
        '
        Me.DataGridView1.AllowUserToAddRows = False
        Me.DataGridView1.AllowUserToDeleteRows = False
        Me.DataGridView1.ColumnHeadersHeightSizeMode = System.Windows.Forms.DataGridViewColumnHeadersHeightSizeMode.AutoSize
        Me.DataGridView1.Location = New System.Drawing.Point(19, 380)
        Me.DataGridView1.Name = "DataGridView1"
        Me.DataGridView1.ReadOnly = True
        Me.DataGridView1.RowHeadersWidth = 51
        Me.DataGridView1.RowTemplate.Height = 24
        Me.DataGridView1.Size = New System.Drawing.Size(944, 213)
        Me.DataGridView1.TabIndex = 3
        '
        'Label4
        '
        Me.Label4.AutoSize = True
        Me.Label4.Font = New System.Drawing.Font("Microsoft Sans Serif", 10.8!, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, CType(0, Byte))
        Me.Label4.Location = New System.Drawing.Point(14, 66)
        Me.Label4.Margin = New System.Windows.Forms.Padding(4, 0, 4, 0)
        Me.Label4.Name = "Label4"
        Me.Label4.Size = New System.Drawing.Size(143, 22)
        Me.Label4.TabIndex = 4
        Me.Label4.Text = "By Kelompok 3"
        '
        'Label3
        '
        Me.Label3.AutoSize = True
        Me.Label3.Font = New System.Drawing.Font("Microsoft Sans Serif", 13.8!, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, CType(0, Byte))
        Me.Label3.Location = New System.Drawing.Point(14, 28)
        Me.Label3.Margin = New System.Windows.Forms.Padding(4, 0, 4, 0)
        Me.Label3.Name = "Label3"
        Me.Label3.Size = New System.Drawing.Size(481, 29)
        Me.Label3.TabIndex = 5
        Me.Label3.Text = "FINGERPRINT ATTENDANCE SYSTEM"
        '
        'timeDisplay
        '
        Me.timeDisplay.AutoSize = True
        Me.timeDisplay.Font = New System.Drawing.Font("Microsoft Sans Serif", 25.8!, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, CType(0, Byte))
        Me.timeDisplay.Location = New System.Drawing.Point(157, 178)
        Me.timeDisplay.Margin = New System.Windows.Forms.Padding(4, 0, 4, 0)
        Me.timeDisplay.Name = "timeDisplay"
        Me.timeDisplay.Size = New System.Drawing.Size(198, 51)
        Me.timeDisplay.TabIndex = 5
        Me.timeDisplay.Text = "00.00.00"
        '
        'todayLabel
        '
        Me.todayLabel.AutoSize = True
        Me.todayLabel.Font = New System.Drawing.Font("Microsoft Sans Serif", 10.2!, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, CType(0, Byte))
        Me.todayLabel.Location = New System.Drawing.Point(160, 136)
        Me.todayLabel.Margin = New System.Windows.Forms.Padding(4, 0, 4, 0)
        Me.todayLabel.Name = "todayLabel"
        Me.todayLabel.Size = New System.Drawing.Size(45, 20)
        Me.todayLabel.TabIndex = 5
        Me.todayLabel.Text = "xxxx"
        '
        'Main
        '
        Me.AutoScaleDimensions = New System.Drawing.SizeF(8.0!, 16.0!)
        Me.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font
        Me.ClientSize = New System.Drawing.Size(1008, 623)
        Me.Controls.Add(Me.Label4)
        Me.Controls.Add(Me.todayLabel)
        Me.Controls.Add(Me.timeDisplay)
        Me.Controls.Add(Me.Label3)
        Me.Controls.Add(Me.DataGridView1)
        Me.Controls.Add(Me.fullNameTxt)
        Me.Controls.Add(Me.Label1)
        Me.Controls.Add(Me.FingerPrintControll1)
        Me.Margin = New System.Windows.Forms.Padding(4)
        Me.Name = "Main"
        Me.Text = "Fingerprint Attendance System"
        CType(Me.DataGridView1, System.ComponentModel.ISupportInitialize).EndInit()
        Me.ResumeLayout(False)
        Me.PerformLayout()

    End Sub
    Friend WithEvents FingerPrintControll1 As fingerPrintControl.fingerPrintControll
    Friend WithEvents Label1 As System.Windows.Forms.Label
    Friend WithEvents fullNameTxt As System.Windows.Forms.TextBox
    Friend WithEvents DataGridView1 As DataGridView
    Friend WithEvents Label4 As Label
    Friend WithEvents Label3 As Label
    Friend WithEvents timeDisplay As Label
    Friend WithEvents Timer1 As Timer
    Friend WithEvents todayLabel As Label
End Class
