(function () {
    'use strict';

    var script = document.createElement('script');

    script.async = true;
    script.src = @json($assetUrl);
    script.dataset.apiKey = @json($apiKey);
    script.dataset.endpoint = @json($endpoint);

    document.head.appendChild(script);
})();
