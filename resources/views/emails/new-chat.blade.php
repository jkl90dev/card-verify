<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle discussion de chat</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f1f5f9; padding: 40px 0;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border: 1px solid #e2e8f0;">
                    <!-- Header -->
                    <tr>
                        <td align="left" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 32px 40px; color: #ffffff;">
                            <div style="font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #93c5fd; margin-bottom: 8px;">CardVerify Support</div>
                            <h1 style="margin: 0; font-size: 24px; font-weight: 800; tracking-tight: -0.025em; line-height: 1.2;">Nouveau Message Chat</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;" align="left">
                            <p style="margin: 0 0 24px 0; font-size: 16px; line-height: 1.6; color: #334155;">
                                Bonjour, <br>
                                Un visiteur vient d'initier une nouvelle discussion sur le chat d'assistance en ligne.
                            </p>
                            
                            <!-- User Metadata Table -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px; border-collapse: collapse;">
                                <tr>
                                    <td width="30%" style="padding: 12px 16px; background-color: #f8fafc; border: 1px solid #cbd5e1; font-weight: 700; font-size: 14px; color: #475569;">Nom de l'utilisateur</td>
                                    <td style="padding: 12px 16px; border: 1px solid #cbd5e1; font-size: 14px; color: #0f172a; font-weight: 600;">{{ $chatSession->user_name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; background-color: #f8fafc; border: 1px solid #cbd5e1; font-weight: 700; font-size: 14px; color: #475569;">Adresse E-mail</td>
                                    <td style="padding: 12px 16px; border: 1px solid #cbd5e1; font-size: 14px; color: #2563eb; font-weight: 600;">
                                        {{ $chatSession->user_email ?: 'Non renseignée' }}
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Message Box -->
                            <h3 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">Premier message envoyé :</h3>
                            <div style="background-color: #f8fafc; border-left: 4px solid #3b82f6; border-radius: 0 8px 8px 0; padding: 20px; font-size: 15px; line-height: 1.6; color: #1e293b; margin-bottom: 32px; font-style: italic; border: 1px solid #e2e8f0; border-left-width: 4px;">
                                "{{ $firstMessage }}"
                            </div>
                            
                            <!-- Action Button -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <a href="{{ url('/agent/chat-dashboard') }}" style="display: inline-block; background-color: #2563eb; color: #ffffff; font-weight: 700; font-size: 14px; text-decoration: none; padding: 14px 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2), 0 2px 4px -1px rgba(37, 99, 235, 0.1); transition: background-color 0.2s;">
                                            Ouvrir la Console Agent
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; border-top: 1px solid #f1f5f9; padding: 24px 40px; text-align: center; font-size: 12px; color: #64748b;">
                            Cet e-mail a été généré automatiquement par le serveur de CardVerify.<br>
                            Veuillez vous assurer que vous disposez du code de sécurité pour vous connecter à la console.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
