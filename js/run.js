var map, markers, layerIDs = [], featureMap = {},
  center = [100.522431, 13.829216], currentId,
  infoPanel = document.getElementById('info-panel'),
  controlPanel = document.getElementById('control-panel'),
  rightButtonContainer = document.getElementById('right-button-container'),
  searchButton = document.getElementById('search-button'),
  bookmarkButton = document.getElementById('bookmark-button'),
  rightPanel = document.getElementById('right-panel'),
  rightPanelHeaderText = document.getElementById('right-panel-header-text'),
  searchPanel = document.getElementById('search-panel'),
  bookmarkPanel = document.getElementById('bookmark-panel'),
  bookmarkInfo = document.getElementById('info-bookmark'),
  bookmarkIcon = document.getElementById('info-bookmark-icon'),
  popup = new mapboxgl.Popup({
    closeButton: false,
    closeOnClick: false
  }),
  bookmarkedIds = [];

function initial(data) {
  markers = data;
  mapboxgl.accessToken = 'pk.eyJ1Ijoia3JhYnJyIiwiYSI6ImNpcDhpY2U4dzAxN3N0am5vbngzaGJybjYifQ.lxKNmQ-0-TSts_CSs22TMg';
  map = new mapboxgl.Map({
    container: 'map',
    center: center,
    zoom: 4.5,
    style: 'mapbox://styles/krabrr/cipbakdga0045dcnk4aom4zt5'
  });
  map.on('load', mapOnLoad);
  map.on('click', mapOnClick);
  map.on('mousemove', mapOnMouseMove);
  searchButton.addEventListener('click', buttonClickedHandler);
  bookmarkButton.addEventListener('click', buttonClickedHandler);
  bookmarkInfo.addEventListener('click', bookmarkClickedHandler);
}

function mapOnLoad() {
  map.addSource('markers', {
    'type': 'geojson',
    'data': markers
  });
  markers.features.forEach(function(feature) {
    var layerID = feature.properties.id;
    featureMap[layerID] = feature;
    map.addLayer({
      'id': layerID,
      'type': 'symbol',
      'source': 'markers',
      'layout': {
        'icon-image': 'green-marker-24',
        'icon-size': 1,
        'icon-offset': [-2, 0],
        'icon-allow-overlap': true,
        'text-field': '{title}',
        'text-size': 15,
        'text-font': ['Quark Bold'],
        'text-offset': [0, 0.9],
        'text-anchor': 'top',
        'text-optional': true
      },
      'paint': {
        'text-halo-color': '#ffffff',
        'text-halo-width': 1
      },
      'filter': ['==', 'id', layerID]
    });
    layerIDs.push(layerID);
  });
}

function mapOnClick(event) {
  var features = map.queryRenderedFeatures(event.point, {'layers': layerIDs});
  if (!features.length) {
    popup.remove();
    infoPanel.style.bottom = '-300px';
    return;
  }
  var feature = features[0];
  popup.setLngLat(feature.geometry.coordinates).setHTML(feature.properties.description).addTo(map);
}

function mapOnMouseMove(event) {
  var features = map.queryRenderedFeatures(event.point, {'layers': layerIDs});
  if (!features.length) {
    return;
  }
  var feature = features[0];
  map.getCanvas().style.cursor = (features.length) ? 'pointer' : '';
}

function showMoreInfo(layerID) {
  var feature = featureMap[layerID];
  if (!feature) {
    return;
  }
  rightButtonContainer.style.right = 0;
  rightPanel.style.right = '-300px';
  infoPanel.style.display = 'block';
  infoPanel.style.bottom = '0';
  var titleInfo = document.getElementById('info-title'),
    dateInfo = document.getElementById('info-date'),
    distanceInfo = document.getElementById('info-distance'),
    linkInfo = document.getElementById('info-link');
  
  currentId = feature.properties.id;
  titleInfo.innerHTML = feature.properties.name;
  dateInfo.innerHTML = '<span class="bold">Date :</span> ' + feature.properties.dateDisplay;
  distanceInfo.innerHTML = '<span class="bold">Distance :</span> ' + feature.properties.distanceDisplay;
  linkInfo.innerHTML = '<span class="bold">Website :</span> <a href="' + feature.properties.link + '">'
    + feature.properties.linkDisplay + '</a>';
  
  if (bookmarkedIds.indexOf(currentId) < 0) {
    bookmarkIcon.className = 'ion-ios-star-outline info-bookmark-icon';
    bookmarkIcon.style.color = '#d3d3d3';
  } else {
    bookmarkIcon.className = 'ion-ios-star info-bookmark-icon';
    bookmarkIcon.style.color = '#f3d35d';
  }
}

function bookmarkClickedHandler(event) {
  if (bookmarkedIds.indexOf(currentId) < 0) {
    bookmarkIcon.className = 'ion-ios-star info-bookmark-icon';
    bookmarkIcon.style.color = '#f3d35d';
    bookmarkedIds.push(currentId);
  } else {
    bookmarkIcon.className = 'ion-ios-star-outline info-bookmark-icon';
    bookmarkIcon.style.color = '#d3d3d3';
    bookmarkedIds.splice(bookmarkedIds.indexOf(currentId), 1);
  }
  updateBookmarkList();
}

function updateBookmarkList() {
  var i, att, bookmarkId, container, icon, node, nodeContent,
    caret, exportButtonContainer, exportButton, menu, item,
    bookmarkPanel = document.getElementById('bookmark-panel');
  
  // remove all child except dropdown
  while (bookmarkPanel.firstChild && bookmarkPanel.firstChild.className != 'btn-group') {
    bookmarkPanel.removeChild(bookmarkPanel.firstChild);
  }
  
  if (!bookmarkedIds.length) {
    node = document.createElement('p');
    nodeContent = document.createTextNode('No Favorite Event');
    node.appendChild(nodeContent);
    bookmarkPanel.insertBefore(node, bookmarkPanel.firstChild);
    return;
  }
  
  for (i = 0; i < bookmarkedIds.length; i++) {
    bookmarkId = bookmarkedIds[i];
    feature = featureMap[bookmarkId];
    if (!feature) {
      continue;
    }
    container = document.createElement('div');
    container.className = 'right-panel-bookmark-container';
    container.style.display = 'flex';
    icon = document.createElement('div');
    icon.className = 'ion-ios-star right-panel-bookmark-icon';
    node = document.createElement('p');
    node.id = bookmarkId;
    node.className = 'right-panel-bookmark-text';
    nodeContent = document.createTextNode(feature.properties.name);
    node.appendChild(nodeContent);
    container.appendChild(icon);
    container.appendChild(node);
    bookmarkPanel.insertBefore(container, bookmarkPanel.firstChild);
  }
}

function buttonClickedHandler(event) {
  var target = event.currentTarget;
  if (target.id == 'search-button') {
    if (target.className == 'right-button') {
      target.className = 'right-button-selected';
      bookmarkButton.className = 'right-button';
    } else {
      target.className = 'right-button';
    }
  } else {
    if (target.className == 'right-button') {
      updateBookmarkList();
      target.className = 'right-button-selected';
      searchButton.className = 'right-button';
    } else {
      target.className = 'right-button';
    }
  }

  toggleRightPanel(target.id);
}

function toggleRightPanel(id) {
  infoPanel.style.bottom = '-300px';
  rightPanel.style.display = 'block';
  var rightPanelStyle = window.getComputedStyle(rightPanel),
    searchPanelStyle = window.getComputedStyle(searchPanel),
    bookmarkPanelStyle = window.getComputedStyle(bookmarkPanel);
  if (rightPanelStyle.getPropertyValue('right') == '-300px') {
    if (id == 'search-button') {
      rightPanelHeaderText.innerHTML = 'Search';
      searchPanel.style.display = 'block';
      bookmarkPanel.style.display = 'none';
    } else {
      rightPanelHeaderText.innerHTML = 'Bookmarks';
      searchPanel.style.display = 'none';
      bookmarkPanel.style.display = 'block';
    }
    rightButtonContainer.style.right = '300px';
    rightPanel.style.right = '0';
  } else {
    if (id == 'search-button') {
      if (searchPanelStyle.getPropertyValue('display') == 'block') {
        rightButtonContainer.style.right = '0';
        rightPanel.style.right = '-300px';
      } else {
        rightPanelHeaderText.innerHTML = 'Search';
        searchPanel.style.display = 'block';
        bookmarkPanel.style.display = 'none';
      }
    } else {
      if (bookmarkPanelStyle.getPropertyValue('display') == 'block') {
        rightButtonContainer.style.right = '0';
        rightPanel.style.right = '-300px';
      } else {
        rightPanelHeaderText.innerHTML = 'Bookmarks';
        searchPanel.style.display = 'none';
        bookmarkPanel.style.display = 'block';
      }
    }
  }
}

function filterMarkers() {
  var keyword = document.getElementById('search-keyword').value,
    date = document.getElementById('search-date').value,
    distanceInputs = document.getElementsByClassName('search-distance'),
    typeInputs = document.getElementsByClassName('search-type'),
    i, distances = [], types = [];

  for (i = 0; distanceInputs[i]; i++){
    if (distanceInputs[i].checked){
      distances.push(distanceInputs[i].value);
    }
  }

  for (var i = 0; typeInputs[i]; i++){
    if (typeInputs[i].checked){
      types.push(typeInputs[i].value);
    }
  }

  layerIDs.forEach(function(layerID) {
    feature = featureMap[layerID];
    var visible = getVisible(feature, keyword, date, distances, types);
    map.setLayoutProperty(layerID, 'visibility', visible);
  });
}

function getVisible(feature, keyword, date, distances, types) {
  var name = feature.properties.name,
    myDate = feature.properties.date,
    myType = feature.properties.type,
    myDistanceStr = feature.properties.distance,
    myDistanceArr = myDistanceStr.split('/'),
    myDistances = [], distance, i, re, valid;

  for (i = 0; i < myDistanceArr.length; i++) {
    myDistances.push(parseFloat(myDistanceArr[i]));
  }

  if (keyword) {
    re = new RegExp(keyword, 'ig')
    if (!name.match(re)) {
      return 'none';
    }
  }

  //TODO: support search by date
  if (date && !isDateValid(myDate, date)) {
    return 'none';
  }

  if (!distances.length) {
    return 'none';
  } else {
    valid = false;
    for (i = 0; i < myDistances.length; i++) {
      distance = myDistances[i];
      if (distance < 10 && distances.indexOf('fun') >= 0) {
        valid = true;
      } else if (distance >= 10 && distance < 20 && distances.indexOf('mini') >= 0) {
        valid = true;
      } else if (distance >= 20 && distance < 40 && distances.indexOf('half') >= 0) {
        valid = true;
      } else if (distance >= 40 && distance < 45 && distances.indexOf('full') >= 0) {
        valid = true;
      } else if (distance >= 45 && distances.indexOf('ultra') >= 0) {
        valid = true;
      }
      if (valid) {
        break;
      }
    }
    if (!valid) {
      return 'none';
    }
  }

  if (!types.length) {
    return 'none';
  } else {
    if (types.indexOf(myType) < 0 && myType != 'others') {
      return 'none';
    }
  }

  return 'visible';
}

function isDateValid(myDateStr, dateStr) {
  var dateArr = dateStr.split('-'),
    fromDateStr, toDateStr, fromDate, toDate;

  if (dateArr.length != 2) {
    return true;  // invalid format, not filter anything
  }

  fromDateStr = dateArr[0];
  toDateStr = dateArr[1];
  fromDate = convertToDate(fromDateStr.split('/'));
  toDate = convertToDate(toDateStr.split('/'));

  if (!fromDate || !toDate) {
    return true;  // invalid format, not filter anything
  }

  var myDate = new Date(myDateStr);
  if (!myDate) {
    return true;  // invalid format, not filter anything
  }

  return (fromDate <= myDate && toDate >= myDate);
}

function convertToDate(parts) {
  if (parts.length != 3) {
    return null;
  }
  if (parts[0] == '' || parts[1] == '' || parts[2] == '') {
    return null;
  }
  if (parts[2].length == 2) {
    parts[2] = '20' + parts[2];
  }
  var day = parseInt(parts[0]),
    month = parseInt(parts[1]),
    year = parseInt(parts[2]);

  return new Date(year, month - 1, day);
}
