<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nachrichten aus 🇩🇪</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Aktuelle Nachrichten</h1>
        <div id="news-container">
            <?php include 'news_fetcher.php'; ?>
        </div>
        <div class="update-info">
            Letzte Aktualisierung: <span id="last-update-time"><?php echo date('H:i:s'); ?></span>
            <div id="countdown-circle" data-time="60"></div>
        </div>
    </div>
    <script>
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            navigator.serviceWorker.register('service-worker.js').then(function(reg) {
                return navigator.serviceWorker.ready;
            }).then(function(reg) {
                Notification.requestPermission().then(function(permission) {
                    if (permission === 'granted') {
                        reg.sync.register('news-fetch');
                    }
                });
            });

            navigator.serviceWorker.addEventListener('message', function(event) {
                if (event.data.type === 'get-titles') {
                    event.ports[0].postMessage({ titles: JSON.parse(localStorage.getItem('titles') || '[]') });
                } else if (event.data.type === 'set-titles') {
                    localStorage.setItem('titles', JSON.stringify(event.data.titles));
                }
            });
        } else {
            console.log('Service Worker or Background Sync not supported');
        }

        function updateNews() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'news_fetcher.php', true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById('news-container').innerHTML = xhr.responseText;
                    var elements = document.getElementsByClassName('last-update-time');
                    for (var i = 0; i < elements.length; i++) {
                        elements[i].textContent = new Date().toLocaleTimeString('de-DE');
                    }
                    document.getElementById('last-update-time').textContent = new Date().toLocaleTimeString('de-DE');
                    resetCountdown();
                }
            };
            xhr.send();
        }

        function resetCountdown() {
            var circle = document.getElementById('countdown-circle');
            circle.setAttribute('data-time', 60);
            circle.style.background = 'conic-gradient(#888 0%, #cecece 100%)';
        }

        function updateCountdown() {
            var circle = document.getElementById('countdown-circle');
            var time = parseInt(circle.getAttribute('data-time'));
            if (time <= 0) {
                updateNews();
            } else {
                var percentage = (time / 60) * 100;
                circle.style.background = 'conic-gradient(#888 0%, #888 ' + percentage + '%, #cecece ' + percentage + '%, #cecece 100%)';
                circle.setAttribute('data-time', time - 1);
            }
        }

        setInterval(updateCountdown, 1000);
        resetCountdown();
    </script>
</body>
</html>
