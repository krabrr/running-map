<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='X-UA-Compatible' content='IE=edge'>
  <meta name='viewport' content='width=device-width, initial-scale=1'>
  <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
  <title>RUNNING MAP</title>
  <script src='https://api.mapbox.com/mapbox-gl-js/v0.19.1/mapbox-gl.js'></script>
	<link href='https://api.mapbox.com/mapbox-gl-js/v0.19.1/mapbox-gl.css' rel='stylesheet'/>
  <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/css/bootstrap.min.css' integrity='sha384-y3tfxAZXuh4HwSYylfB+J125MxIs6mR5FOHamPBG064zB+AFeWH94NdvaCBm8qnd' crossorigin='anonymous'>

  <link rel='stylesheet' href='http://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css'>
  <link rel='stylesheet' href='css/map.css'>
  <link rel='stylesheet' href='css/run.css'>
  <link rel='shortcut icon' href='images/favicon.png'>

  <meta property='og:title' content='Running Map' />
  <meta property='og:url' content='http://nbaramichai.com/running-map/' />
  <meta property='og:description' content='Map for runner' />
  <meta property='og:site_name' content='nbaramichai' />
  <meta property='og:image' content='http://nbaramichai.com/running-map/images/preview.jpg' />
  <meta property='fb:app_id' content='1571321539835406' />
</head>
<body>
  <script>
    window.fbAsyncInit = function() {
      FB.init({
        appId      : '1571321539835406',
        xfbml      : true,
        version    : 'v2.6'
      });
    };

    (function(d, s, id){
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) {return;}
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_US/sdk.js";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
  </script>

  <?php
    $conn = mysqli_connect('www.nbaramichai.com', 'running', 'iloveeatingafterrun', 'running');
    mysqli_query($conn, 'SET NAMES UTF8MB4');
    if (!$conn) {
      die(mysqli_connect_error());
    } else {
      $query = 'SELECT * FROM events';
      if ($result = mysqli_query($conn, $query)) {
        $features = [];
        while ($row = mysqli_fetch_assoc($result)) {
          $id = $row['id'];
          $name = $row['name'];
          $date = $row['date'];
          $link = $row['link'];
          $link_display = '';
          if (strlen($link) > 40) {
            $link_display = substr($link, 0, 40) . '...';
          } else {
            $link_display = $link;
          }

          $latitude = $row['latitude'];
          $longitude = $row['longitude'];
          $type = $row['type'];

          $distance = $row['distance'];
          if ($distance == '-') {
            $distance_display = '- KM';
          } else {
            $distance_display = str_replace('/', 'KM / ', $distance) . 'KM';
          }

          $date_time = strtotime($date);
          $date_display = Date('d F Y', $date_time);

          $description = "<div class='popup'>";
          $description .= "<p class='title'>$name</p>";
          $description .= "<p>Date : $date_display</p>";
          $description .= "<p>Distance : $distance_display</p>";
          $description .= "<p class=\"read-more\"><a href=\"#\" onclick=\"showMoreInfo($id);return false\">Read more</a></p>";
          $description .= "</div>";

          $geometry = ['type' => 'Point', 'coordinates' => [$longitude, $latitude]];
          $properties = ['id' => $id, 'title' => $name, 'description' => $description, 'name' => $name,
                         'date' => $date, 'dateDisplay' => $date_display, 'distance' => $distance,
                         'distanceDisplay' => $distance_display, 'type' => $type, 'link' => $link,
                         'linkDisplay' => $link_display];

          $feature = ['type' => 'Feature', 'geometry' => $geometry, 'properties' => $properties];
          $features[] = $feature;
        }
        $data = ['type' => 'FeatureCollection', 'features' => $features];
        $data = json_encode($data);
      } else {
        die(mysqli_error($conn));
      }
    }
  ?>

  <!-- <div id='control-panel'>
    <div class='form-group'>
      <label for='from-date'>From Date</label>
      <input type='text' class='form-control' id='from-date' placeholder='DD/MM/YYYY eg. 01/01/2016'>
    </div>
    <div class='form-group'>
      <label for='to-date'>To Date</label>
      <input type='text' class='form-control' id='to-date' placeholder='DD/MM/YYYY eg. 01/12/2016'>
    </div>
  </div> -->

  <div class='info-panel' id='info-panel'>
    <button type='button' id='info-close' class='close info-close' data-dismiss='modal'>&times;</button>
    <div id='info-container'>
      <h4 id='info-title'></h4>
      <p id='info-date'></p>
      <p id='info-distance'></p>
      <p id='info-link'></p>
      <div id ='info-bookmark' class='info-bookmark'>
        <div id='info-bookmark-icon' class='ion-ios-star-outline info-bookmark-icon'></div>
        <div class='info-bookmark-text-container'>
          <span class='info-bookmark-text'>Bookmark</span>
        </div>
      </div>
    </div>
  </div>

  <div id='right-button-container' class='right-button-container'>
    <div id='search-button' class='right-button'>
      <!-- <img src='images/search.png' alt='Search'> -->
      <p id='search-icon' class='ion-ios-search-strong'>
    </div>
    <div id='bookmark-button' class='right-button'>
      <!-- <img src='images/star.png' alt='Bookmark'> -->
      <p id='bookmark-icon' class='ion-star'>
    </div>
  </div>

  <div id='right-panel' class='right-panel'>
    <div id='right-panel-header'>
      <p id='right-panel-header-text'></p>
    </div>
    <div id='search-panel' class='right-panel-content'>
      <form class='search'>
        <div class='form-group'>
          <label for='search-keyword'>Search by keyword :</label>
          <input type='text' class='form-control' id='search-keyword' placeholder='keyword...'>
        </div>
        <div class='form-group'>
          <label for='search-date'>Search by date :</label>
          <input type='text' class='form-control' id='search-date' placeholder='date (e.g. 1/8/16 - 1/9/16)'>
        </div>
        <div class='form-group'>
          <label>Search by distance :</label>
          <p><input class='search-distance' type='checkbox' name='distance' value='fun' checked><span class='checkbox-label'>Fun Run (&lt;10KM)</span></p>
          <p><input class='search-distance' type='checkbox' name='distance' value='mini' checked><span class='checkbox-label'>Mini Marathon (10KM)</span></p>
          <p><input class='search-distance' type='checkbox' name='distance' value='half' checked><span class='checkbox-label'>Half Marathon (21.1KM)</span></p>
          <p><input class='search-distance' type='checkbox' name='distance' value='full' checked><span class='checkbox-label'>Marathon (42.195KM)</span></p>
          <p><input class='search-distance' type='checkbox' name='distance' value='ultra' checked><span class='checkbox-label'>Ultra Marathon (&gt;42.195KM)</span></p>
        </div>
        <div class='form-group'>
          <label>Search by distance :</label>
          <p><input class='search-type' type='checkbox' name='type' value='road' checked><span class='checkbox-label'>Road Running</span></p>
          <p><input class='search-type' type='checkbox' name='type' value='trail' checked><span class='checkbox-label'>Trail Running</span></p>
          <p><input class='search-type' type='checkbox' name='type' value='tri' checked><span class='checkbox-label'>Triathlon</span></p>
          <p><input class='search-type' type='checkbox' name='type' value='others' checked><span class='checkbox-label'>Others</span></p>
        </div>
        <input id='search-submit-button' class='btn btn-primary' type='button' value='Search' onclick='filterMarkers()'>
      </form>
    </div>

    <div id='bookmark-panel' class='right-panel-content'>
      <!-- Single button -->
      <div class='btn-group'>
        <button type='button' class='btn btn-secondary dropdown-toggle right-panel-export-bookmark' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
          Export Events
        </button>
        <div class='dropdown-menu'>
          <a id='ics' class='dropdown-item' href='#'>.ics</a>
          <a id='csv' class='dropdown-item' href='#'>.csv</a>
        </div>
      </div>

      <a class='bookmark-help' data-toggle='modal' data-target='#myModal'>How to import events to your calendar</a>

    </div>
  </div>

  <!-- Modal -->
  <div id='myModal' class='modal fade' role='dialog'>
    <div class='modal-dialog'>

      <!-- Modal content-->
      <div class='modal-content'>
        <div class='modal-header'>
          <button type='button' class='close' data-dismiss='modal'>&times;</button>
          <h4 class='modal-title'>How to import events to your calendar</h4>
        </div>
        <div class='modal-body'>
          <p class='help-text'>1. Bookmark your favorite events.</p>
          <img class='help-img' src='images/help-1.jpg' alt='help-1'>
          <p class='help-text'>2. Click export button.</p>
          <img class='help-img' src='images/help-2.jpg' alt='help-2'>
          <p class='help-text'>3. If you use Mac, just click the .ics file.</p>
          <img class='help-img' src='images/help-3.jpg' alt='help-3'>
          <p class='help-text'>4. It will open Calendar App. Select destination calender and click ok.</p>
          <img class='help-img' src='images/help-4.jpg' alt='help-4'>
          <p class='help-text'>5. All your favorite events will be added to calendar.</p>
          <img class='help-img' src='images/help-5.jpg' alt='help-5'>
          <p class='help-text'>6. For Google Calendar. Click gear icon and then click setting.</p>
          <img class='help-img' src='images/help-6.jpg' alt='help-6'>
          <p class='help-text'>7. Click Calendars.</p>
          <img class='help-img' src='images/help-7.jpg' alt='help-7'>
          <p class='help-text'>8. Click Import calendar.</p>
          <img class='help-img' src='images/help-8.jpg' alt='help-8'>
          <p class='help-text'>9. Select .ics or .cvs file to import. Select destination calendar and click import.</p>
          <img class='help-img' src='images/help-9.jpg' alt='help-9'>
          <p class='help-text'>10. All your favorite events will be added to calendar</p>
          <img class='help-img' src='images/help-10.jpg' alt='help-10'>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Done</button>
        </div>

      </div>

    </div>
  </div>

  <div id='map'></div>
  <script src='js/run.js'></script>
  <?php echo "<script>initial($data);</script>"; ?>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.2/js/bootstrap.min.js" integrity="sha384-vZ2WRJMwsjRMW/8U7i6PWi6AlO1L79snBrmgiDpgIWJ82z8eA5lenwvxbMV1PAh7" crossorigin="anonymous"></script>
</body>
</html>
