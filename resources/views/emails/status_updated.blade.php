<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour de votre demande</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        .header {
            padding: 32px;
            text-align: center;
        }
        .header.validated {
            background-color: #059669;
            color: #ffffff;
        }
        .header.rejected {
            background-color: #dc2626;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .content {
            padding: 32px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
            color: #ffffff !important;
        }
        .btn.validated {
            background-color: #059669;
        }
        .btn.rejected {
            background-color: #dc2626;
        }
        .details {
            background-color: #f1f5f9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
        }
        .footer {
            padding: 20px 32px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header {{ $voucherRequest->status }}">
            <h1>VoucherVerify</h1>
        </div>
        <div class="content">
            <p>Bonjour,</p>
            <p>Le traitement de votre demande de vérification de voucher est terminé.</p>
            
            <div class="details">
                <strong>Résultat de la vérification :</strong><br>
                • Type de Voucher : {{ $voucherRequest->voucherType->name }}<br>
                • Statut final : <strong>{{ $voucherRequest->status === 'validated' ? 'VALIDÉ' : 'REJETÉ' }}</strong><br>
                
                @if($voucherRequest->status === 'validated' && $voucherRequest->agent_comment)
                    • Commentaire : {{ $voucherRequest->agent_comment }}<br>
                @endif

                @if($voucherRequest->status === 'rejected')
                    • Motif : {{ $voucherRequest->rejection_reason }}<br>
                    @if($voucherRequest->rejection_detail)
                        • Précisions : {{ $voucherRequest->rejection_detail }}<br>
                    @endif
                @endif
            </div>

            <div style="text-align: center;">
                <a href="{{ url('/track/' . $voucherRequest->tracking_token) }}" class="btn {{ $voucherRequest->status }}">Voir les détails de mon ticket</a>
            </div>

            <p>Merci pour votre confiance,<br>L'équipe VoucherVerify</p>
        </div>
        <div class="footer">
            Ceci est un message automatique, merci de ne pas y répondre directement.
        </div>
    </div>
</body>
</html>
