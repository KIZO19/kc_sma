<?php require __DIR__ . '/../partials/app_header.php'; ?>

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Vérifier solvabilité</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/dashboard">Accueil</a></li>
          <li class="breadcrumb-item active">Vérifier solvabilité</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-md-8 col-xl-7">
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4">
          <div class="text-center mb-4">
            <h2 class="h4 fw-bold text-primary mb-3">
              <i class="bi bi-qr-code-scan me-2"></i>Vérification de solvabilité
            </h2>
            <p class="text-muted mb-0">Présentez le QR code du reçu devant la caméra pour afficher le total payé et la dette totale de l’élève.</p>
          </div>

          <div id="solvency-qr-reader" class="mx-auto border border-3 border-primary rounded-4 shadow-sm w-100" style="max-width: 500px; overflow: hidden;"></div>
          <div id="solvency-scan-status" class="mt-4 p-3 rounded-3 alert alert-info" role="status" aria-live="polite">
            Autorisez l’accès à la caméra pour commencer le scan.
          </div>

          <hr class="my-4">

          <form method="get" action="<?= BASE_URL ?>/paiements/qrSummary" id="solvency-receipt-form">
            <label for="solvency-receipt-id" class="form-label fw-semibold">Rechercher par identifiant du paiement</label>
            <div class="input-group">
              <input
                type="text"
                class="form-control"
                id="solvency-receipt-id"
                name="id"
                placeholder="Ex. 15 ou legacy-15"
                autocomplete="off"
                required
              >
              <button type="submit" class="btn btn-success">
                <i class="bi bi-search me-1"></i>Rechercher
              </button>
            </div>
            <div class="form-text">Vous pouvez aussi coller le lien du QR code dans ce champ.</div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php $pageScripts = <<<'SCRIPT'
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const status = document.getElementById('solvency-scan-status');
    const receiptInput = document.getElementById('solvency-receipt-id');
    const form = document.getElementById('solvency-receipt-form');
    let processingScan = false;
    const scanner = new Html5QrcodeScanner('solvency-qr-reader', {
      fps: 10,
      qrbox: 250,
    });

    function showStatus(message, type) {
      status.className = 'mt-4 p-3 rounded-3 alert alert-' + type;
      status.textContent = message;
    }

    function extractReceiptId(rawValue) {
      let receiptId = String(rawValue || '').trim();
      if (/^https?:\/\//i.test(receiptId)) {
        try {
          const scannedUrl = new URL(receiptId);
          if (!/\/paiements\/qrsummary\/?$/i.test(scannedUrl.pathname)) {
            return null;
          }
          receiptId = scannedUrl.searchParams.get('id') || '';
        } catch (error) {
          return null;
        }
      }

      return /^(?:legacy-)?[1-9]\d*$/.test(receiptId) ? receiptId : null;
    }

    function submitReceipt(rawValue, fromScan) {
      const receiptId = extractReceiptId(rawValue);
      if (!receiptId) {
        showStatus('Ce code ne contient pas un identifiant de reçu valide.', 'danger');
        return false;
      }

      receiptInput.value = receiptId;
      if (fromScan) {
        processingScan = true;
        showStatus('Reçu détecté. Chargement de la vérification…', 'success');
        scanner.clear()
          .then(function () {
            form.submit();
          })
          .catch(function (error) {
            console.error('Impossible d’arrêter le scanner QR :', error);
            form.submit();
          });
      } else {
        form.submit();
      }
      return true;
    }

    scanner.render(function (decodedText) {
      if (processingScan) return;
      submitReceipt(decodedText, true);
    }, function () {
      // Html5Qrcode calls this for each frame without a decoded QR code.
    });

    form.addEventListener('submit', function (event) {
      const receiptId = extractReceiptId(receiptInput.value);
      if (!receiptId) {
        event.preventDefault();
        showStatus('Saisissez un identifiant valide ou collez le lien du QR code.', 'danger');
        return;
      }
      receiptInput.value = receiptId;
    });
  });
</script>
SCRIPT;
require __DIR__ . '/../partials/app_footer.php'; ?>
