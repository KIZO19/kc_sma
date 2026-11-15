using System;
using System.Diagnostics;
using System.Drawing;
using System.IO;
using System.Windows.Forms;

internal static class Program
{
    private static readonly string AppDirectory = Path.Combine(
        Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData),
        "KC-SMA");

    private static readonly string AddressFile = Path.Combine(AppDirectory, "server-url.txt");

    [STAThread]
    private static void Main(string[] args)
    {
        Application.EnableVisualStyles();
        Application.SetCompatibleTextRenderingDefault(false);

        string savedAddress = "";
        if (File.Exists(AddressFile))
        {
            try
            {
                savedAddress = File.ReadAllText(AddressFile).Trim();
            }
            catch (Exception ex)
            {
                ShowError("Impossible de lire l'adresse du serveur : " + ex.Message);
                Environment.ExitCode = 1;
                return;
            }
        }

        string serverUrl = NormalizeServerUrl(savedAddress);
        bool configure = Array.Exists(args, arg => string.Equals(arg, "/configurer", StringComparison.OrdinalIgnoreCase));
        if (serverUrl == null || configure)
        {
            serverUrl = ShowAddressDialog(savedAddress);
            if (string.IsNullOrEmpty(serverUrl))
            {
                return;
            }

            try
            {
                Directory.CreateDirectory(AppDirectory);
                File.WriteAllText(AddressFile, serverUrl, new System.Text.UTF8Encoding(false));
            }
            catch (Exception ex)
            {
                ShowError("Impossible d'enregistrer l'adresse du serveur : " + ex.Message);
                Environment.ExitCode = 1;
                return;
            }
        }

        try
        {
            Process.Start(serverUrl);
        }
        catch (Exception ex)
        {
            ShowError("Impossible d'ouvrir l'application : " + ex.Message);
            Environment.ExitCode = 1;
        }
    }

    private static string NormalizeServerUrl(string value)
    {
        Uri uri;
        if (string.IsNullOrWhiteSpace(value) || !Uri.TryCreate(value.Trim(), UriKind.Absolute, out uri))
        {
            return null;
        }

        if ((uri.Scheme != Uri.UriSchemeHttp && uri.Scheme != Uri.UriSchemeHttps) ||
            string.IsNullOrEmpty(uri.Host) ||
            !string.IsNullOrEmpty(uri.UserInfo) ||
            !string.IsNullOrEmpty(uri.Query) ||
            !string.IsNullOrEmpty(uri.Fragment))
        {
            return null;
        }

        return uri.GetLeftPart(UriPartial.Authority) + uri.AbsolutePath.TrimEnd('/') + "/";
    }

    private static string ShowAddressDialog(string initialAddress)
    {
        using (Form form = new Form())
        {
            form.Text = "KC-SMA - Connexion au serveur";
            form.StartPosition = FormStartPosition.CenterScreen;
            form.FormBorderStyle = FormBorderStyle.FixedDialog;
            form.MaximizeBox = false;
            form.MinimizeBox = false;
            form.ClientSize = new Size(600, 150);
            form.Font = SystemFonts.MessageBoxFont;

            Label label = new Label();
            label.Text = "Adresse complete de votre application sur le serveur :";
            label.AutoSize = true;
            label.Location = new Point(18, 15);
            form.Controls.Add(label);

            Label example = new Label();
            example.Text = "Exemple : http://192.168.1.10/kc_sma/public/";
            example.AutoSize = true;
            example.Location = new Point(18, 38);
            form.Controls.Add(example);

            TextBox addressBox = new TextBox();
            addressBox.Location = new Point(18, 62);
            addressBox.Size = new Size(564, 24);
            addressBox.Text = initialAddress;
            form.Controls.Add(addressBox);

            string result = null;
            Button openButton = new Button();
            openButton.Text = "Ouvrir";
            openButton.Location = new Point(382, 105);
            openButton.Size = new Size(95, 28);
            openButton.Click += (sender, eventArgs) =>
            {
                string normalizedUrl = NormalizeServerUrl(addressBox.Text);
                if (normalizedUrl == null)
                {
                    MessageBox.Show(
                        form,
                        "Saisissez une adresse HTTP ou HTTPS valide, sans identifiant ni parametre.",
                        "Adresse invalide",
                        MessageBoxButtons.OK,
                        MessageBoxIcon.Warning);
                    addressBox.Focus();
                    return;
                }

                result = normalizedUrl;
                form.DialogResult = DialogResult.OK;
                form.Close();
            };
            form.Controls.Add(openButton);

            Button cancelButton = new Button();
            cancelButton.Text = "Annuler";
            cancelButton.Location = new Point(487, 105);
            cancelButton.Size = new Size(95, 28);
            cancelButton.DialogResult = DialogResult.Cancel;
            form.Controls.Add(cancelButton);

            form.AcceptButton = openButton;
            form.CancelButton = cancelButton;
            form.Shown += (sender, eventArgs) => addressBox.Focus();
            return form.ShowDialog() == DialogResult.OK ? result : null;
        }
    }

    private static void ShowError(string message)
    {
        MessageBox.Show(message, "KC-SMA", MessageBoxButtons.OK, MessageBoxIcon.Error);
    }
}
