<?php

function summon_tools__request(
    $query,
    $content_type,
    $overrideQuery = false,
    $offset = 0,
    $limit = 3,
    $sort = false,
    $searchIncludes = false,
) {
    /* Returns response in $doctype format (xml|json) from Summon 2.0 API for a $query.

    - Results start at $offset for a total of $limit results.
    - Results are sorted by relevance ($sort = False) or date-descending ($sort = True).
    - Code based on Python code here: https://gist.github.com/lawlesst/1070641
    - See also: http://blog.humaneguitarist.org/2014/09/04/getting-started-with-the-summon-api-and-python/
    */

    // create query string.
    $query = "s.q=" . $query . "&s.cmd=addFacetValueFilters%28ContentType%2C$content_type%29&s.pn=$offset&s.ps=$limit&s.ho=true";


    // set sort to date-descending if needed.
    if ($sort != false) {
        $query = $query . "&s.sort=PublicationDate:desc";
    }
    if($searchIncludes != false) {
        $query = $query . $searchIncludes;
    }

    if ($overrideQuery) {
        $query = $overrideQuery;
    }
    return requestFromSummon($query);
}

function requestFromSummon($queryString)
{
    $summonSettings = get_field('summon_settings', 'option');
    $api_id = $summonSettings['api_id'];
    $api_key = $summonSettings['api_key'];
    $host = $summonSettings['host'];
    $path = $summonSettings['path'];
    $url = "http://$host$path?$queryString";
    $timeout = 10;
    $headers = getSummonHeaders($queryString, $api_id, $api_key, $host, $path);
    return url_tools__request($url, $timeout, $headers);
}

function getSummonHeaders($queryString, $api_id, $api_key, $host, $path)
{
    $query_sorted = explode("&", $queryString);
    asort($query_sorted);
    $query_sorted = implode("&", $query_sorted);
    $query_encoded = urldecode($query_sorted);

    // create request headers.
    $accept = "application/json";
    $date = gmdate('D, d M Y H:i:s \G\M\T');
    $id_string = implode("\n", array($accept, $date, $host, $path, $query_encoded, ""));
    $digest = base64_encode(hash_hmac("sha1", utf8_encode($id_string), $api_key, true));
    $authorization = "Summon " . $api_id . ";" . $digest;
    return [
        "Host:$host",
        "Accept:$accept",
        "x-summon-date:$date",
        "Authorization:$authorization"
    ];
}

function url_tools__request($url, $timeout = 10, $headers = [])
{
    /* Returns results of calling a given $url with a $timeout and optional $headers. */
    // make cURL request; return results.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //suppress output.
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}