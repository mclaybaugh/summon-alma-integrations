# Alma/Summon integrations and lessons learned

## Search Anything / Bento Boxes

Example: https://www.liberty.edu/library/search/?q=whale

A user can see results from nine different sources including four Summon searches for different types of items, databases and research guides, journals, and more.

Key takeaways:

1. Summon query code in PHP was drawn from open source examples, the summon-api.php file includes some comments about this. It uses cURL for the HTTP requests.
2. To enable the searches to remain relevant over time without code changes, most parameters sent in the queries are options that can be edited in the site admin.
3. Because the page has multiple queries running that can take some time, and some queries can only start when others finish, they are all done in the browser through JavaScript HTTP requests. The JavaScript sends the queries to a REST API on the site, and the site then makes the necessary HTTP requests to whatever the resource is and returns the results to the browser. Until they are complete, loading animations display in the boxes. This allows the page to load quickly at first and then progressively fill in results as they are available.
4. For database and research guide results the library staff maintain a mapping of call number ranges to specific databases and research guides. When results are returned in the Books or Articles sections, the call numbers of those results are used to find databases that are related to the query. This data map is time-consuming to load and search, so it is cached on the server to speed up processing.


## Kiosk Page

Examples: https://www.liberty.edu/library/kiosk-page/, https://www.liberty.edu/library/curriculum-kiosk-page/

Provides a search experience that is used on computers physically in the library to allow searching for items. It is a page on the website instead of using Summon directly so that users cannot navigate to areas that make it difficult to return back to the search. These computers have limited functionality and so if users navigate somewhere other than the search page or Alma, it can be difficult to return back to the search page. 

Key takeaways:

1. Uses same summon request code as the Search Anything tool above.
2. Enables each search to use a predetermined scope or set of parameters that can be edited in the admin. There are two variations of the page. One specifically for items in the curriculum library, and the other for the whole library. Both variations have search filters that are appropriate for their locations.
3. When putting in the links to Alma for each item, we customize the View ID (vid) of the Alma URL so that the Alma page loads with a back button specifically made for the kiosk interface. This back button will allow users to return to the search page without the use of the browser "back" button that is disabled on these kiosks. (code in kiosk-alma-url-handling.php). Each Kiosk variation has its own Alma View, and the Time-out URL is set to the respective kiosk page URL.
4. JS added to Alma view to show the back button:
```js
var app = angular.module('viewCustom', ['angularLoad']);
app.component('prmLogoAfter', {
    template: `<button onclick="history.back();" class="kioskHome__backBtn">
    <i class="fas fa-chevron-left"></i> Back
    </button>`
});
```