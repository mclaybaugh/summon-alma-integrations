<script>
function getResourceAlmaLink(document): Promise {
    if (!document.link) {
        document.almaLink = document.link;
        return Promise.resolve(document);
    } else {
        const encodedLink = encodeURIComponent(document.link);
        const url = wpApiSettings.root + 'library/v1/kiosk-alma-url?url=' + encodedLink;
        return fetch(url)
            .then(ensureHttpValid)
            .then(data => {
                if (data) {
                    const almaUrl = new URL(data);
                    const almaSearch = almaUrl.search;
                    const almaSearchParams = new URLSearchParams(almaSearch);
                    /* global ALMA_VID */
                    almaSearchParams.set('vid', ALMA_VID);
                    // create a link with the correct VID parameter
                    document.almaLink = almaUrl.origin + almaUrl.pathname + '?' + almaSearchParams.toString();
                } else {
                    document.almaLink = document.link;
                }
                return document;
            })
            .catch(err => {
                console.warn(err);
                return document;
            });
    }
}
</script>
<?php 

// This function is called by the request to library/v1/kiosk-alma-url and it 
// returns the Alma URL that Summon provides
function kioskAlmaUrl($request)
{
    $originalUrl = $request['url'];
    $args = [
        'allow_redirects' => false,
        'timeout' => 5, // timeout in seconds
    ];
    // academicsHttpAddon_get is a wrapper for the Guzzle Get functionality
    $response = academicsHttpAddon_get($originalUrl, $args);
    if ($response->hasHeader('Location')) {
        $returnUrl = $response->getHeader('Location')[0];
    } else {
        $returnUrl = $originalUrl;
    }
    return $returnUrl;
}