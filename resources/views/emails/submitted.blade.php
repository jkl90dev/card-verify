<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Ticket Reçu</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f5f9;
            color: #334155;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .header {
            background-color: #2563eb;
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .content {
            padding: 24px;
            line-height: 1.6;
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 8px;
            margin-top: 24px;
            margin-bottom: 12px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: 600;
            padding: 6px 0;
            color: #475569;
            width: 35%;
        }

        .info-value {
            display: table-cell;
            padding: 6px 0;
            color: #0f172a;
        }

        .code-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px;
            border-radius: 8px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 16px;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 0.05em;
            word-break: break-all;
            margin-top: 4px;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-activation {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-refund {
            background-color: #fef3c7;
            color: #92400e;
        }

        .footer {
            padding: 16px 24px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Notification de Nouveau Ticket — CardVerify</h1>
        </div>
        <div class="content">
            <p style="margin-top: 0;">Une nouvelle demande a été soumise sur la plateforme. Voici les détails reçus :
            </p>

            <table cellpadding="0" cellspacing="0" border="0"
                style="width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 25px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 14px; text-align: left; border: 1px solid #cbd5e1; background-color: #ffffff;">
                <thead>
                    <tr style="background-color: #1e3a8a; color: #ffffff;">
                        <th style="padding: 12px 15px; font-weight: 700; border: 1px solid #cbd5e1; width: 35%;">Champ
                        </th>
                        <th style="padding: 12px 15px; font-weight: 700; border: 1px solid #cbd5e1;">Détail / Valeur
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background-color: #ffffff;">
                        <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: 600; color: #475569;">ID
                            de la Demande</td>
                        <td
                            style="padding: 12px 15px; border: 1px solid #cbd5e1; font-family: 'Courier New', Courier, monospace; font-weight: bold; color: #334155;">
                            {{ $voucherRequest->id }}
                        </td>
                    </tr>
                    <tr style="background-color: #f8fafc;">
                        <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: 600; color: #475569;">
                            Type de Demande</td>
                        <td style="padding: 12px 15px; border: 1px solid #cbd5e1;">
                            @if($voucherRequest->request_type === 'refund')
                                <span
                                    style="display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a;">Remboursement</span>
                            @else
                                <span
                                    style="display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;">Activation</span>
                            @endif
                        </td>
                    </tr>
                    <tr style="background-color: #ffffff;">
                        <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: 600; color: #475569;">Nom
                            / Pseudo</td>
                        <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: bold; color: #0f172a;">
                            {{ $voucherRequest->fullname }}
                        </td>
                    </tr>
                    <tr style="background-color: #f8fafc;">
                        <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: 600; color: #475569;">
                            Adresse E-mail</td>
                        <td style="padding: 12px 15px; border: 1px solid #cbd5e1; color: #2563eb; font-weight: 600;">
                            {{ $voucherRequest->email_user }}
                        </td>
                    </tr>
                    @if($voucherRequest->phone)
                        <tr style="background-color: #ffffff;">
                            <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: 600; color: #475569;">
                                Téléphone</td>
                            <td style="padding: 12px 15px; border: 1px solid #cbd5e1; color: #0f172a;">
                                {{ $voucherRequest->phone }}
                            </td>
                        </tr>
                    @endif
                    <tr style="background-color: {{ $voucherRequest->phone ? '#f8fafc' : '#ffffff' }};">
                        <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: 600; color: #475569;">
                            Type de Coupon</td>
                        <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: bold; color: #1e293b;">
                            {{ $voucherRequest->voucherType->name }}
                        </td>
                    </tr>
                    @if($voucherRequest->amount)
                        <tr style="background-color: {{ $voucherRequest->phone ? '#ffffff' : '#f8fafc' }};">
                            <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: 600; color: #475569;">
                                Montant indiqué</td>
                            <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: bold; color: #16a34a;">
                                {{ $voucherRequest->amount }}
                            </td>
                        </tr>
                    @endif
                    <tr style="background-color: #ffffff;">
                        <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: 600; color: #475569;">
                            Code 1 (Principal)</td>
                        <td
                            style="padding: 12px 15px; border: 1px solid #cbd5e1; font-family: 'Courier New', Courier, monospace; font-size: 15px; font-weight: bold; color: #1e3a8a; background-color: #f8fafc; letter-spacing: 0.05em; word-break: break-all;">
                            {{ $voucherRequest->code1 }}
                        </td>
                    </tr>
                    @if($voucherRequest->code2)
                        <tr style="background-color: #f8fafc;">
                            <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: 600; color: #475569;">
                                Code 2 (Optionnel)</td>
                            <td
                                style="padding: 12px 15px; border: 1px solid #cbd5e1; font-family: 'Courier New', Courier, monospace; font-size: 15px; font-weight: bold; color: #1e3a8a; background-color: #f8fafc; letter-spacing: 0.05em; word-break: break-all;">
                                {{ $voucherRequest->code2 }}
                            </td>
                        </tr>
                    @endif
                    @if($voucherRequest->user_comment)
                        <tr style="background-color: #ffffff;">
                            <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: 600; color: #475569;">
                                Commentaire utilisateur</td>
                            <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-style: italic; color: #4b5563;">
                                {{ $voucherRequest->user_comment }}
                            </td>
                        </tr>
                    @endif
                    <tr style="background-color: #f8fafc;">
                        <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: 600; color: #475569;">
                            Date de réception</td>
                        <td style="padding: 12px 15px; border: 1px solid #cbd5e1; color: #64748b;">
                            {{ $voucherRequest->created_at->format('d/m/Y à H:i:s') ?? 'N/A' }}
                        </td>
                    </tr>
                    <tr style="background-color: #ffffff;">
                        <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: 600; color: #475569;">IP
                            du Client</td>
                        <td
                            style="padding: 12px 15px; border: 1px solid #cbd5e1; color: #64748b; font-family: 'Courier New', Courier, monospace;">
                            {{ request()->ip() }}
                        </td>
                    </tr>
                    <tr style="background-color: #f8fafc;">
                        <td style="padding: 12px 15px; border: 1px solid #cbd5e1; font-weight: 600; color: #475569;">
                            Navigateur (User Agent)</td>
                        <td style="padding: 12px 15px; border: 1px solid #cbd5e1; color: #64748b; font-size: 12px;">
                            {{ request()->userAgent() }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="footer">
            Cet e-mail a été généré automatiquement par le serveur de CardVerify.<br>
            Veuillez ne pas y répondre directement.
        </div>
    </div>
</body>

</html>