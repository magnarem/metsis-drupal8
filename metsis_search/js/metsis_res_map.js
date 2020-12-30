console.log("Start of metsis search map script:");
(function($, Drupal, drupalSettings) {

  console.log("Attaching map script to drupal behaviours:");
  /** Attach the metsis map to drupal behaviours function */
  Drupal.behaviors.metsisSearchBlock = {
    attach: function(context, drupalSettings) {
      $('#map-res', context).each(function() {
        //$('#map-res', context).once('metsisSearchBlock').each(function() {
        /** Start reading drupalSettings sent from the mapblock build */
        console.log('Initializing METSIS Map...');

        //Default Zoom value
        var defzoom = 4;




        // Import variables from drupalSettings send by block build array
        var extracted_info = drupalSettings.metsis_search_map_block.extracted_info;
        var path = drupalSettings.metsis_search_map_block.path;
        var pins = drupalSettings.metsis_search_map_block.pins;
        var site_name = drupalSettings.metsis_search_map_block.site_name;

        var lat = drupalSettings.metsis_search_map_block.mapLat;
        var lon = drupalSettings.metsis_search_map_block.mapLon;
        var mapZoom = drupalSettings.metsis_search_map_block.mapZoom;
        var bboxFilter = drupalSettings.metsis_search_map_block.bboxFilter;
        var mapFilter = drupalSettings.metsis_search_map_block.mapFilter;

        var init_proj = drupalSettings.metsis_search_map_block.init_proj;
        var projections = drupalSettings.metsis_search_map_block.projections;
        var layers_list = drupalSettings.metsis_search_map_block.layers_list;
        var additional_layers = drupalSettings.metsis_search_map_block.additional_layers;
        var tllat = drupalSettings.metsis_search_map_block.tllat;
        var tllon = drupalSettings.metsis_search_map_block.tllon;
        var brlat = drupalSettings.metsis_search_map_block.brlat;
        var brlon = drupalSettings.metsis_search_map_block.brlon;
        var selected_proj = drupalSettings.metsis_search_map_block.proj;
        var base_layer_wms_north = drupalSettings.metsis_search_map_block.base_layer_wms_north;
        var base_layer_wms_south = drupalSettings.metsis_search_map_block.base_layer_wms_south;
        var pywpsUrl = drupalSettings.metsis_search_map_block.pywps_service;

        // Some debugging
        var debug = true;
        if (debug) {
          console.log("Reading drupalSettings: ")
          console.log('base layer north: ' + base_layer_wms_north);
          console.log('base layer south: ' + base_layer_wms_south);
          console.log('show pins :' + pins);
          console.log('show additional layers: ' + additional_layers);
          console.log('init proj: ' + init_proj);
          console.log('current selected  projection: ' + selected_proj);
          console.log('current bbox: ' + brlat + ',' + brlon + ',' + tllat + ',' + tllon);
          console.log('current map_filter: ' + mapFilter);
          console.log('current bbox_filter: ' + bboxFilter);
          console.log('initial map zoom: ' + mapZoom);
        }

        //Set the configured zoom level as the same as default:
        defZoom = mapZoom;
        //Set current selected projection to initial projection if not altered by user $session
        if (selected_proj == null) {
          var selected_proj = init_proj;
          var proj = init_proj;
        } else {
          var proj = selected_proj;
        }
        // Create the  map baselayer input boxses
        $('.basemap-wrapper').append(
          $(document.createElement('input')).prop({
            key: 'OSMStandard',
            name: 'baseLayerRadioButton',
            value: 'OSMStandard',
            type: 'radio',
            checked: true
          }) //.attr("checked", "")
        ).append(
          $(document.createElement('label')).prop({
            class: "basemap-labels",
            for: 'OSMStandard'
          }).html('OSMStandard')
        );
        $('.basemap-wrapper').append(
          $(document.createElement('input')).prop({
            key: 'OSMHumanitarian',
            name: 'baseLayerRadioButton',
            value: 'OSMHumanitarian',
            type: 'radio',
          })
        ).append(
          $(document.createElement('label')).prop({
            class: "basemap-labels",
            for: 'OSMHumanitarian'
          }).html('OSMHumanitarian')
        );

        $('.basemap-wrapper').append(
          $(document.createElement('input')).prop({
            key: 'stamenTerrain',
            name: 'baseLayerRadioButton',
            value: 'stamenTerrain',
            type: 'radio',
          })
        ).append(
          $(document.createElement('label')).prop({
            class: "basemap-labels",
            for: 'stamenTerrain'
          }).html('StamenTerrain')
        );
        $('.basemap-wrapper').append(
          $(document.createElement('input')).prop({
            key: 'ESRI',
            name: 'baseLayerRadioButton',
            value: 'ESRI',
            type: 'radio',
          })
        ).append(
          $(document.createElement('label')).prop({
            class: "basemap-labels",
            for: 'ESRI'
          }).html('ESRI Satellite')
        );

        // Do some styling
        $('.basemap-labels').css({
          "display": "inline-block",
          "font-weight": "normal",
          //"padding-left": "0px",
          "padding-right": "10px",
          "vertical-align": "middle"
        });
        $('.basemap-wrapper').css({
          "padding-left": "0px",
          "padding-right": "0px",
          "vertical-align": "middle"
        });

        // Create the projections input boxes
        for (var key in projections) {
          var value = projections[key];
          $('.proj-wrapper').append(
            $(document.createElement('input')).prop({
              id: key,
              name: 'map-res-projection',
              value: key,
              type: 'radio',
              class: 'projections'
            })
          ).append(
            $(document.createElement('label')).prop({
              class: "proj-labels",
              for: key
            }).html(value)
          );
        }
        // Do some styling
        $('.proj-labels').css({
          "display": "inline-block",
          "font-weight": "normal",
          "padding-right": "10px",
          "vertical-align": "middle"
        });
        $('.projections').css({
          "padding-left": "10px",
          "padding-right": "0px",
          "vertical-align": "middle"
        });

        //If additional lyers are set, create the layers dropdown button list
        if (additional_layers) {
          console.log('Creating additonal layers dropdown  button');
          $('.layers-wrapper').append(
            $(document.createElement('div')).prop({
              id: 'droplayers',
              class: 'layers'
            }));
          $('#droplayers').append(
            $(document.createElement('button')).prop({
              class: 'layers-button',
              onclick: "document.getElementById('lrs').classList.toggle('show')",
            }).html('Layers'));
          $('#droplayers').append(
            $(document.createElement('div')).prop({
              id: "lrs",
              class: "panel dropdown-lrs-content",
            }));
          $('#lrs').append(
            $(document.createElement('ul')).prop({
              id: "lrslist"
            }));

          for (var key in layers_list) {
            var value = layers_list[key];
            console.log("Creating additional layer: " + value);
            $('#lrslist').append(
              $(document.createElement('li')).prop({
                class: 'addl'
              })
              .append(
                $(document.createElement('input')).prop({
                  id: value,
                  class: 'check-layers',
                  type: 'checkbox',
                  value: value,
                  name: "layers"
                }))
              .append(
                $(document.createElement('label')).prop({
                  class: "layer-labels",
                  for: value
                }).html(value))
            );
          }
          //Add event listener to layers button
          $(".layers-button").click(function() {
            document.getElementById('lrs').classList.toggle('show');
          });
          //Do some styling
          $('.layer-labels').css({
            "display": "inline-block",
            "font-weight": "normal",
            "padding-right": "10px",
            "vertical-align": "middle"
          });
          $('.check-layers').css({
            "vertical-align": "middle",
            "padding-right": "5px",
          });

        }

        //display current bbox search filter

        $('.current-bbox-filter').append('Current filter: ' + mapFilter);
        if (bboxFilter != null) {
          $('.current-bbox-select').append(bboxFilter);
        }

        /**
         * Define the proj4 map_projections
         */
        //console.log(proj4);
        // two projections will be possible
        // 32661
        proj4.defs('EPSG:32661', '+proj=stere +lat_0=90 +lat_ts=90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +datum=WGS84 +units=m +no_defs');
        ol.proj.proj4.register(proj4);
        var ext32661 = [-6e+06, -3e+06, 9e+06, 6e+06];
        var center32661 = [0, 80];
        var proj32661 = new ol.proj.Projection({
          code: 'EPSG:32661',
          extent: ext32661
        });

        // 32761
        proj4.defs('EPSG:32761', '+proj=stere +lat_0=-90 +lat_ts=-90 +lon_0=0 +k=0.994 +x_0=2000000 +y_0=2000000 +ellps=WGS84 +datum=WGS84 +units=m +no_defs');
        ol.proj.proj4.register(proj4);
        var ext32761 = [-8e+06, -8e+06, 12e+06, 10e+06];
        var center32761 = [0, -90];
        var proj32761 = new ol.proj.Projection({
          code: 'EPSG:32761',
          extent: ext32761
        });


        // 4326
        var ext4326 = [-350.0000, -100.0000, 350.0000, 100.0000];
        var center4326 = [15, 0];
        var proj4326 = new ol.proj.Projection({
          code: 'EPSG:4326',
          extent: ext4326
        });

        var projObjectforCode = {
          'EPSG:4326': {
            extent: ext4326,
            center: center4326,
            projection: proj4326
          },
          'EPSG:32661': {
            extent: ext32661,
            center: center32661,
            projection: proj32661
          },
          'EPSG:32761': {
            extent: ext32761,
            center: center32761,
            projection: proj32761
          }
        };

        /** Register event listener for baseMap selection */
        const baseLayerElements = document.querySelectorAll(' .basemap-wrapper > input[type=radio]');
        for (let baseLayerElement of baseLayerElements) {
          baseLayerElement.addEventListener('change', function() {
            let baseLayerValue = this.value;
            console.log("Changing baselayer to: " + baseLayerValue);
            baseLayerGroup.getLayers().forEach(function(element, index, array) {
              let baseLayerTitle = element.get('title');
              element.setVisible(baseLayerTitle === baseLayerValue);
            });
          })
        }



        /** Register event listner when Projection is changed.
         * Rebuild pins and polygons and update map view */
        var ch = document.getElementsByName('map-res-projection');
        document.getElementById(init_proj).checked = true;
        for (var i = ch.length; i--;) {
          ch[i].onchange = function change_projection() {
            prj = this.value;
            proj = prj;
            selected_proj = proj;
            console.log("change projection event: " + prj);

            //Remove pins ans polygons
            console.log("Remove pins and polygons layers");
            if (pins) {
              map.getLayers().remove(featureLayers['pins']);
            }
            map.getLayers().remove(featureLayers['polygons']);


            /** Refresh additonal defined layers */
            //Adding try catch to aviod errors when layers are not defined
            /*  try {
                if (additional_layers) {
                  console.log("Refreshing additional layers");
                  layer['europaveg'].getSource().refresh();
                  layer['fylkesveg'].getSource().refresh();
                  layer['riksveg'].getSource().refresh();
                }
              } catch (e) {
                console.log('additional layers already removed');
              }
              */
            //rebuild vector source
            //createOverViewMap(prj)
            console.log("Rebuild pins and polygons features with projection: " + prj);
            var featuresExtent = buildFeatures(projObjectforCode[prj].projection);
            console.log("Update view to new selected projection: " + prj);
            console.log("Features extent: " + featuresExtent);
            map.setView(new ol.View({
              center: ol.extent.getCenter(featuresExtent),
              extent: projObjectforCode[prj].extent,
              projection: projObjectforCode[prj].projection,
              //projection: prj,
            }));
            map.getView().fit(featuresExtent);
            map.getView().setZoom(map.getView().getZoom() - 0.3);
          }

        }

        //Create a popup with information:
        /**
         * Elements that make up the popup.
         */
        var popUpContainer = document.getElementById('popup');
        //var content = document.getElementById('popup-content');
        var popUpContent = $("#popup-content");
        var popUpCloser = document.getElementById('popup-closer');

        /**
         * Create an overlay to anchor the popup to the map.
         */
        console.log("Creating popup overlay");
        var popUpOverlay = new ol.Overlay({
          element: popUpContainer,
          autoPan: true,
          autoPanAnimation: {
            duration: 150,
          },
        });

        /**
         * Add a click handler to hide the popup.
         * @return {boolean} Don't follow the href.
         */
        console.log("Register popUp closer event");
        popUpCloser.onclick = function() {
          popUpOverlay.setPosition(undefined);
          popUpCloser.blur();
          return false;
        };


        /** Add tooltip overlay to map */
        if (debug) {
          console.log("Creating tooltip overlay");
        }
        // title on hover tooltip
        var tlphovMapRes = document.createElement("div");
        tlphovMapRes.setAttribute("id", "tlphov-map-res")

        var overlayh = new ol.Overlay({
          element: tlphovMapRes,
        });
        //  map.addLayer(layer['OSM']);
        //map.addOverlay(overlayh);

        /** Create custom features and styles */

        //in nbs s1-ew
        var featureStyleBl = new ol.style.Style({
          fill: new ol.style.Fill({
            color: 'rgba(0,0,255,0.1)',
          }),
          stroke: new ol.style.Stroke({
            color: 'blue',
            width: 2
          }),
        });

        var featureStyleGr = new ol.style.Style({
          fill: new ol.style.Fill({
            color: 'rgba(186, 168, 168,0.1)',
          }),
          stroke: new ol.style.Stroke({
            color: 'gray',
            width: 2
          }),
        });

        var iconStyleBl = new ol.style.Style({
          image: new ol.style.Icon(({
            anchor: [0.5, 0.0],
            anchorOrigin: 'bottom-left',
            anchorXUnits: 'fraction',
            anchorYUnits: 'fraction',
            opacity: 1.00,
            src: '/' + path + '/icons/pinBl.png'
          }))
        });

        var iconStyleGr = new ol.style.Style({
          image: new ol.style.Icon(({
            anchor: [0.5, 0.0],
            anchorOrigin: 'bottom-left',
            anchorXUnits: 'fraction',
            anchorYUnits: 'fraction',
            opacity: 1.00,
            src: '/' + path + '/icons/pinGr.png'
          }))
        });

        var iconStyleBk = new ol.style.Style({
          image: new ol.style.Icon(({
            anchor: [0.5, 0.0],
            anchorOrigin: 'bottom-left',
            anchorXUnits: 'fraction',
            anchorYUnits: 'fraction',
            opacity: 1.00,
            src: '/' + path + '/icons/pinBk.png'
          }))
        });


        /**
         * Define different basemaps layers to choose from here.
         * Using layergroups and radio selection
         */

        const osmStandard = new ol.layer.Tile({
          title: 'OSMStandard',
          baseLayer: true,
          visible: true,
          source: new ol.source.OSM({}),
        });

        const osmHumanitarian = new ol.layer.Tile({
          title: 'OSMHumanitarian',
          baseLayer: true,
          visible: false,
          source: new ol.source.OSM({
            url: 'https://{a-c}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
            crossOrigin: 'anonymous',
          }),
        });
        const yandex = new ol.layer.Tile({
          title: "Yandex",
          baseLayer: true,
          visible: false,
          source: new ol.source.XYZ({
            url: 'https://sat0{1-4}.maps.yandex.net/tiles?l=sat&x={x}&y={y}&z={z}',
            maxZoom: 15,
            transition: 0,
            //opaque: true,
            attributions: '© Yandex',
            crossOrigin: 'anonymous',
          }),
        });

        const esriSatellite = new ol.layer.Tile({
          title: "ESRI",
          baseLayer: true,
          visible: false,
          source: new ol.source.XYZ({
            attributions: ['Powered by Esri',
              'Source: Esri, DigitalGlobe, GeoEye, Earthstar Geographics, CNES/Airbus DS, USDA, USGS, AeroGRID, IGN, and the GIS User Community'
            ],
            attributionsCollapsible: false,
            url: 'https://services.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            maxZoom: 23,
            crossOrigin: 'anonymous',
          }),
        });

        const stamenTerrain = new ol.layer.Tile({
          title: "stamenTerrain",
          baseLayer: true,
          visible: false,
          source: new ol.source.XYZ({
            attributions: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, under <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a>. Data by <a href="http://openstreetmap.org">OpenStreetMap</a>, under <a href="http://www.openstreetmap.org/copyright">ODbL</a>.',
            url: 'https://stamen-tiles.a.ssl.fastly.net/terrain/{z}/{x}/{y}.jpg',
            crossOrigin: 'anonymous',
          }),
        });

        //Create a layergroup to hold the different basemaps
        const baseLayerGroup = new ol.layer.Group({
          layers: [
            osmStandard, osmHumanitarian, stamenTerrain, esriSatellite
          ],
        });

        // create layergroup to hold wmsLayers
        //const wmsLayerGroup = new ol.layer.Group();

        //Create features Layergroup
        var featureLayers = {};
        var featureLayersGroup = new ol.layer.Group({
          title: 'Features',
          layers: [],
        });

        //Add overviewMap
        var bboxLayer = getActiveBbox(selected_proj);
        var ovMapLayers = [];
        var ovBaseLayer = new ol.layer.Tile({
          //baseLayer: true,
          visible: true,
          source: new ol.source.OSM(),
          projection: selected_proj,
        });
        ovMapLayers.push(ovBaseLayer);
        if (bboxLayer != null) {
          console.log("Adding bbox to overviewMap");
          ovMapLayers.push(bboxLayer);
        }

        //Add MapControls

        //Add OverVoewMapControl
        var ovMapControl = new ol.control.OverviewMap({
          //className: 'ol-overviewmap bboxViewMap',
          title: 'overviewMap',
          layers: ovMapLayers,
          collapsed: true,
        });

        //Add fullScreenControl
        var fullScreenControl = new ol.control.FullScreen();

        //Add scaleline control
        var scaleLineControl = new ol.control.ScaleLine();
        //Initialize the map
        console.log("Creating the map");
        var map = new ol.Map({
          target: 'map-res',
          pixelRatio: 1,
          controls: ol.control.defaults().extend([ovMapControl, fullScreenControl, scaleLineControl]),
          //controls: ol.control.defaults().extend([fullScreenControl]),
          //layers: [baseLayerGroup,featureLayersGroup],
          layers: [baseLayerGroup],
          overlays: [overlayh, popUpOverlay],
          view: new ol.View({
            zoom: defzoom,
            minZoom: 0,
            maxZoom: 23,
            //rotation: 0.5,
            center: projObjectforCode[selected_proj].center,
            extent: projObjectforCode[selected_proj].extent,
            projection: projObjectforCode[selected_proj].projection,
            //projection: selected_proj,
          }),
        });





        //map.addLayer(wms_layer);


        /** Callback function for tooltip pointer move event
         Display the title */
        function id_tooltip_h() {
          console.log("Register tooltip hover function.")
          map.on('pointermove', function(evt) {
            var coordinate = evt.coordinate;
            var feature_ids = {};
            map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
              //console.log(feature);
              feature_ids[feature.get('id')] = {
                title: feature.get('title'),
                id: feature.get('id')
              };
            });
            if (feature_ids.length !== 0) {
              tlphovMapRes.style.display = 'inline-block';
              tlphovMapRes.innerHTML = '';
              for (var id in feature_ids) {
                overlayh.setPosition(coordinate);
                overlayh.setPositioning('top-left');
                overlayh.setOffset([0, 20]);
                if (pins) {
                  tlphovMapRes.innerHTML += feature_ids[id].title + '<br>';
                } else {
                  tlphovMapRes.innerHTML += feature_ids[id].id + '<br>';
                }
              }
            } else {
              tlphovMapRes.style.display = 'hidden';
            }
          });
        }



        /**
         * TODO: Read this helptext from metsis search configuration */
        $('#bottomMapPanel').append().text('Interact directly with selected products from the map by clicking on the highlighted features.');
        // clickable ID in tooltop
        //var tlpMapRes = document.createElement("div");
        //tlpMapRes.setAttribute("id", "tlp-map-res");
        //document.getElementById("map-res").appendChild(tlpMapRes);

        var toolclickevent;
        var toolclickevent_new;
        var getProductInfo;

        //Th plost time series manin function
        function plot_ts(url_o, md_ts_id, path, pywps) {
          let loader = '<img class="map-ts-plot-loader" src="/' + path + '/icons/loader.gif">';
          $('#bokeh-map-ts-plot').find('.map-ts-loader').append(loader);
          var variable = $('#bokeh-map-ts-plot').find('#map-ts-var-list').val();
          if ($('#map-ts-plot').html().length > 0) {
            $('#map-ts-plot').empty();
          }

          fetch(pywps + '?get=plot&resource_url=' + url_o + '&variable=' + variable + '&axis=' + $('#bokeh-map-ts-plot').find('#axis').val())
            .then(function(response) {
              return response.json();
            })
            .then(function(item) {
              item.target_id = 'map-ts-plot';
              Bokeh.embed.embed_item(item);
              $('#bokeh-map-ts-plot').find('.map-ts-loader').empty();
            })
        }

        //Function to plot timeSeries reqistered as variable plotTimeserie, used in getProductInfo
        function plotTimeseries(opendap_url) {
          console.log("calling ts-plot with url: " + opendap_url);

          //Hide SearchMap
          $('#search-map').slideUp();
          $('#map-ts-back').unbind('click');
          $('#map-ts-back').empty();

          //Show ts-bokeh plot:
          $('#bokeh-map-ts-plot').slideDown();

          //Create back to results button:
          var button = $('#map-ts-back').append(
            $(document.createElement('button')).prop({
              id: 'backToMapButton',
              class: "w3-button w3-small",
            }).html('Back to results map')
          );
          // Register action for click button:
          button.on('click', function() {
            $('#bokeh-map-ts-plot').slideUp();
            $('#search-map').slideDown();
            $('#map-ts-plot').empty();
            $('#map-ts-var-list').unbind('change');
            $('#bokeh-map-ts-plot').find('.map-ts-vars').empty();
            $('#backToMapButton').unbind('click');
            $('#map-ts-back').empty();
          });

        /*  if ($('#map-ts-plot').html().length > 0 || $('#bokeh-map-ts-plot').find('.map-ts-vars').html().length > 0) {
            $('#map-ts-plot').empty();
            $('#bokeh-map-ts-plot').find('.map-ts-vars').empty();
          } else {*/
            let loader = '<img class="ts-click-loader" src="/core/misc/throbber-active.gif">';
            $('#bokeh-map-ts-plot').find('.map-ts-loader').append(loader);
            console.log('fetching variables');
            fetch(pywpsUrl + '?get=param&resource_url=' + opendap_url)
              .then(response => response.json())
              .then(data => {
                $('#bokeh-map-ts-plot').find('.map-ts-vars').html(
                  $(document.createElement('input')).prop({
                    id: 'axis',
                    name: 'axis',
                    value: Object.keys(data),
                    type: 'hidden',
                  })
                ).append(
                  $(document.createElement('select')).prop({
                    id: 'map-ts-var-list',
                    name: 'var_list',

                  }).append(
                    $(document.createElement('option')).text('Choose variable')
                  )
                );
                console.log('looping variables');
                for (const variable of data[Object.keys(data)]) {
                  var el = document.createElement("option");
                  el.textContent = variable;
                  el.value = variable;
                  $('#bokeh-map-ts-plot').find('#map-ts-var-list').append(el);
                }
                $('#bokeh-map-ts-plot').find('.map-ts-loader').empty();

                $('#bokeh-map-ts-plot').find('#map-ts-var-list').on('change', function() {
                  plot_ts(opendap_url, id, path, pywpsUrl)

                });

              });
          //}
        }

        //Function for retrieving wms capabilities
        function getWmsLayers(wmsUrl, title) {
          var wmsLayerGroup = new ol.layer.Group({
            title: title,
            layers: [],
          });
          if (wmsUrl != null && wmsUrl != "") {
            //console.log("Got wms resource: " +wmsUrl);
            //console.log("Parsing getCapabilties");
            var getCapString = '?SERVICE=WMS&VERSION=1.3.0&REQUEST=GetCapabilities';
            var parser = new ol.format.WMSCapabilities();
            //Do xml request
            let xhr = new XMLHttpRequest();

            xhr.open('GET', '/metsis/map/getcapfromurl?url=' + wmsUrl,['sync'])
            xhr.setRequestHeader("Content-Type", "application/xml")
            xhr.setRequestHeader('Accept', 'application/xml')
            xhr.setRequestHeader('Access-Control-Allow-Origin', '*')
            xhr.send()
            // 4. This will be called after the response is received
            xhr.onload = function() {
              if (xhr.status != 200) { // analyze HTTP status of the response
                console.log(`Error ${xhr.status}: ${xhr.statusText}`); // e.g. 404: Not Found
              } else { // show the result
                //console.log(xhr.response); // response is the server response
                var result = parser.read(xhr.response);
                console.log(result);
                //var options = ol.source.WMS.optionsFromCapabilities(result);
                var defaultProjection = result.Capability.Layer.CRS;
                var layers = result.Capability.Layer.Layer;
                var bbox = result.Capability.Layer.EX_GeographicBoundingBox;
                //console.log(defaultProjection);
                //console.log(layers);
                //console.log(bbox);
                for (var idx = 0; idx < layers.length; idx++) {
                  var ls = layers[idx].Layer;
                  if (ls) {
                    for (var i = 0; i < ls.length; i++) {
                      var getTimeDimensions = function() {
                        var dimensions = ls[i].Dimension;
                        if (ls[i].Dimension) {
                          for (var j = 0; j < dimensions.length; j++) {
                            if ("time" === dimensions[j].name) {
                              var times = dimensions[j].values.split(",");
                              return times;
                            }
                          }
                        }
                        return [];
                      };
                      var makeAxisAwareExtent = function() {
                        var bboxs = ls[i].BoundingBox;
                        if (bboxs) {
                          for (var k = 0; k < bboxs.length; k++) {
                            if (result.version === '1.3.0' && bboxs[k].crs === 'EPSG:4326') {
                              //switch minx with min y and max x with maxy
                              var axisAwareExtent = [];
                              axisAwareExtent[0] = bboxs[k].extent[1];
                              axisAwareExtent[1] = bboxs[k].extent[0];
                              axisAwareExtent[2] = bboxs[k].extent[3];
                              axisAwareExtent[3] = bboxs[k].extent[2];
                              return axisAwareExtent;
                            }
                          }
                        }
                        return bboxs[0].extent;
                      };


                      var layerProjections = ls[i].CRS;
                      wmsLayerGroup.getLayers().push(
                        new ol.layer.Tile({
                          title: ls[i].Title,
                          visible: true,
                          keepVisible: false,
                          //projections: ol.control.Projection.CommonProjections(outerThis.projections, (layerProjections) ? layerProjections : wmsProjs),
                          dimensions: getTimeDimensions(),
                          styles: ls[i].Style,
                          source: new ol.source.TileWMS(({
                            url: wmsUrl,
                            params: {
                              'LAYERS': ls[i].Name,
                              'VERSION': result.version,
                              'FORMAT': 'image/png',
                              'STYLES': (typeof ls[i].Style !== "undefined") ? ls[i].Style[0].Name : '',
                              'TILE': true,
                              'TRANSPARENT': true,
                            },
                            crossOrigin: 'anonymous',

                          })),
                        }));
                    }
                  }
                }
                return wmsLayerGroup;
              }
            };

            xhr.onerror = function() {
              alert("Request failed");
            };
            //console.log(layers);

            return wmsLayerGroup;
          }
        }

        /** Action when product is selected on map
         * Show /Hide datasets in results list on search pages
         *
         * More functionality to be added
         */
        function getProductInfo(evt) {
          //overlay.setPosition([coordinate[0] + coordinate[0] * 20 / 100, coordinate[1] + coordinate[1] * 20 / 100]);
          $('.datasets-row').css('display', 'none');
          console.log('getProductInfo event');
          var feature_ids = {};
          var feature_wms = {};
          var id = null;

          //Define layer names for sentinel products
          var sentinel1Layers = ['Composites'];
          var sentinel2Layers = ['true_color_vegetation', 'false_color_vegetation', 'false_color_glacier', 'false_color_glacier', 'opaque_clouds', 'cirrus_clouds'];
          //Clear the previous popup content:
          $('#popup-content').empty();
          //$('#popup-content').hide();

          popUpOverlay.setPosition(undefined);

          //Get the current event coordinate
          var coordinate = evt.coordinate;
          //overlayh.setPosition([coordinate[0] + coordinate[0] * 20 / 100, coordinate[1] + coordinate[1] * 20 / 100]);
          //Foreach feature selected. do the following
          map.forEachFeatureAtPixel(evt.pixel, function(feature, layer) {
            console.log("Clicked feature: " + feature.get('name'));
            /* Show / Hide results depending on selected dataset in map */
            id = feature.get('id');
            newId = id.replace(/_/g, "-");
            //alert(newId);
            $('.datasets-' + newId).css('display', 'block');
            //$('._'+newId).css('display', 'block');
            $(document).ready(function() {
              $('li.datasets-' + newId).focus();
            });
            // $(feature.get('id')).each(function() {
            //$(this).css('display', 'block');
            //});
            //});

            //Reload the lazy loading of thumbnails
            var bLazy = new Blazy();
            bLazy.revalidate();


            console.log("Got " + feature.get('name') + " at coordinate:");
            console.log(coordinate);
            //overlay.setPosition([coordinate[0] + coordinate[0] * 20 / 100, coordinate[1] + coordinate[1] * 20 / 100]);
            /** WMS RENDER ON CLICK */
            //IF selected Product have WMS layer. Render this WMS and Zoom to extent.
            feature_ids[feature.get('id')] = {
              url_o: feature.get('url')[0],
              url_w: feature.get('url')[1],
              url_h: feature.get('url')[2],
              url_od: feature.get('url')[3],
              url_dln: feature.get('url')[4],
              url_dlo: feature.get('url')[5],
              id: feature.get('id'),
              extent: feature.get('extent'),
              latlon: feature.get('latlon'),
              title: feature.get('title'),
              timeStart: feature.get('time')[0],
              timeEnd: feature.get('time')[1],
              featureType: feature.get('feature_type'),
              name: feature.get('name'),
              geom: feature.getGeometry(),
            };
          });
          //Add Popup if selected more than one feature:
          var numberOfFeatures = Object.keys(feature_ids).length;
          console.log("Number of selected features: " + numberOfFeatures);
          if (numberOfFeatures === 0) {
            $('.datasets-row').css('display', 'block');
          }
          if (numberOfFeatures === 1) {
            console.log("Execute action for ONE feature");
            var wmsResource = feature_ids[id].url_w;
            var odResource = feature_ids[id].url_o;
            var featureType = feature_ids[id].featureType;
            var title = feature_ids[id].title;

            console.log('product_id: ' + id);
            console.log('product title: ' + feature_ids[id].title);
            console.log('wms resource: ' + wmsResource);
            console.log('latlon: ' + feature_ids[id].latlon);
            console.log('extent: ' + feature_ids[id].extent);
            console.log('feature type: ' + feature_ids[id].featureType);
            console.log('projection: ' + selected_proj);

            //Check for timeseries product
            if (odResource != null && odResource != "") {
              if (feature_ids[id].featureType === 'timeSeries' || feature_ids[id].featureType === 'profile') {
                console.log("Got timeseries product: " + feature_ids[id].id);
                $('#popup-content').append("<p>" + feature_ids[id].title + "</p>");
                var button = $('#popup-content').append(
                  $(document.createElement('button')).prop({
                    class: "w3-button w3-small",
                  }).html('Visualise timeseries')

                );

                console.log("Alter the popUpOverlay position.");
                popUpOverlay.setPosition(coordinate);
                button.on('click', function() {
                  plotTimeseries(odResource)
                });
              }
            }

            //Check WMS product:
            if (wmsResource != null && wmsResource != "") {
              console.log("Got WMS product: " + feature_ids[id].id);

              //TODO: Do more stuff here with the WMS product
              //var wmsLayers = getWmsLayers(wmsResource, title);
              //console.log(wmsLayers);

              var wmsLayerGroup = new ol.layer.Group({
                title: title,
                layers: [],
              });
              wmsLayerGroup.getLayers().push(
                new ol.layer.Tile({
                  //title: ls[i].Title,
                  visible: true,
                  keepVisible: false,
                  //projections: ol.control.Projection.CommonProjections(outerThis.projections, (layerProjections) ? layerProjections : wmsProjs),
                  //dimensions: getTimeDimensions(),
                  //styles: ls[i].Style,
                  source: new ol.source.TileWMS(({
                    url: wmsResource,
                    params: {
                      'LAYERS': 'Composites',
                      'VERSION': '1.3.0',
                      'FORMAT': 'image/png',
                      //'STYLES': (typeof ls[i].Style !== "undefined") ? ls[i].Style[0].Name : '',
                      'TILE': true,
                      'TRANSPARENT': true,
                    },
                    crossOrigin: 'anonymous',

                  })),
                }));
      /*        var wmsLayer = new ol.layer.Tile({
                title: feature_ids[id].title,
                //extent: projObjectforCode[proj].extent,
                crossOrigin: 'anonymous',
                source: new ol.source.TileWMS({
                  url: wmsResource,
                  projection: selected_proj,
                  //projection: projObjectforCode[proj].projection,
                  reprojectionErrorThreshold: 0.1,
                  params: {
                    'LAYERS': 'Composites',
                    //'LAYERS': 'WMS',
                    //'FORMAT': 'image/png',
                    //'VERSION': '1.3.0',
                    //'CRS': 'EPSG:4326',
                    'TILE': true,
                    'TRANSPARENT': true,
                  },
                  crossOrigin: 'anonymous',
                }),
              });
*/
              /*
              var areaLayer = new ol.layer.Tile({
                title: 'area-'+feature_ids[id].title,
                //extent: projObjectforCode[proj].extent,
                source: new ol.source.TileWMS( {
                  url: wmsResource,
                  //url: 'https://thredds.nersc.no/thredds/wms/normap/arctic8km_adt_aggr',
                  //projection: proj,
                  //projection: projObjectforCode[proj].projection,
                  reprojectionErrorThreshold: 0.1,
                  params: {
                    'LAYERS': 'area',
                    //'LAYERS': 'WMS',
                    //'FORMAT': 'image/png',
                    //'VERSION': '1.3.0',
                    //'CRS': 'EPSG:4326',
                    'TILE': true,
                    'TRANSPARENT': true,
                  },
                  crossOrigin: 'anonymous',
                }),
              });
              */
              //console.log(wmsLayer);
              //Fit to feature
              //wmsLayerGroup.setLayers([wmsLayer]);
              map.addLayer(wmsLayerGroup);

              //Hide the style of the selected feature.
              var pinFeatures = featureLayers['pins'].getSource().getFeatures();
              var polygonFeatures = featureLayers['polygons'].getSource().getFeatures();

              for (let pinFeature of pinFeatures) {
                if (pinFeature.getId() === id) {
                  pinFeature.setStyle();
                }
              }
              for (let polygonFeature of polygonFeatures) {
                if (polygonFeature.getId() === id) {
                  polygonFeature.setStyle();
                }
              }
              //map.addLayer(areaLayer);
              //Hide Pins and polygons
              featureLayers['pins'].setVisible(false);
              featureLayers['polygons'].setVisible(false);

            /*  map.getLayers().forEach(function(element, index, array) {

                if (element.get('title') === 'pins') {
                  element.setVisible(false);
                }
                if (element.get('title') === 'polygons') {
                  element.setVisible(false);
                }
              })
*/

              //Fit to feature geometry
              //console.log(feature_ids[id]);
              map.getView().fit(feature_ids[id].geom.getExtent());
              //map.getView().fit(wmsLayer.getExtent())
              map.getView().setZoom(map.getView().getZoom());
            }

          }
          if (numberOfFeatures > 1) {
            console.log("Execute action for multiple features: " + numberOfFeatures);
            for (var key in feature_ids) {
              console.log(feature_ids[key].id);
              $('#popup-content').append(feature_ids[key].title);
            }

            console.log("Alter the popUpOverlay position.");
            popUpOverlay.setPosition(coordinate);
          } else {
            console.log("No feature selected");
              //$('.datasets-row').css('display', 'block');
          }

          //});
        }


        function id_tooltip() {
          //var tooltip = document.getElementById('tlp-map-res');
          console.log('inside id_tooltip');

          map.on('click', tooltipclick);
        }

        function id_tooltip_new() {
          //var tooltip = document.getElementById('tlp-map-res');
          console.log('Register product select event');

          map.on('singleclick', getProductInfo);
        }

        //build up the point/polygon features
        function buildFeatures(prj) {
          console.log("Building polygons and pins features....");
          //console.log(prj);
          var allFeatures = [];
          var iconFeaturesPol = [];
          var iconFeaturesPin = [];
          var wmsProducts = [];
          for (var i12 = 0; i12 <= extracted_info.length - 1; i12++) {

            //If we have a geographic extent, create polygon feature
            if ((extracted_info[i12][2][0] !== extracted_info[i12][2][1]) || (extracted_info[i12][2][2] !== extracted_info[i12][2][3])) {
              //Transform boundingbox to selected projection and create a polygon geometry
              box_tl = ol.proj.transform([extracted_info[i12][2][3], extracted_info[i12][2][0]], 'EPSG:4326', prj);
              box_tr = ol.proj.transform([extracted_info[i12][2][2], extracted_info[i12][2][0]], 'EPSG:4326', prj);
              box_bl = ol.proj.transform([extracted_info[i12][2][3], extracted_info[i12][2][1]], 'EPSG:4326', prj);
              box_br = ol.proj.transform([extracted_info[i12][2][2], extracted_info[i12][2][1]], 'EPSG:4326', prj);
              geom = new ol.geom.Polygon([
                [box_tl, box_tr, box_br, box_bl, box_tl]
              ]);

              //Define polygon features
              var iconFeaturePol = new ol.Feature({
                url: extracted_info[i12][0],
                id: extracted_info[i12][1],
                geometry: geom,
                extent: [extracted_info[i12][2][0], extracted_info[i12][2][1], extracted_info[i12][2][2], extracted_info[i12][2][3]],
                latlon: extracted_info[i12][3],
                title: extracted_info[i12][4],
                abs: extracted_info[i12][5],
                time: [extracted_info[i12][6][0], extracted_info[i12][6][1]],
                thumb: extracted_info[i12][7],
                related_info: extracted_info[i12][8],
                iso_keys_coll_act: extracted_info[i12][9],
                info_status: extracted_info[i12][10],
                data_center: extracted_info[i12][11],
                actions: extracted_info[i12][12],
                contacts: extracted_info[i12][13],
                constraints: extracted_info[i12][14],
                core: extracted_info[i12][15],
                feature_type: extracted_info[i12][16],
                name: "Polygon Feature",
                //projection: prj,
              });
              iconFeaturePol.setId(extracted_info[i12][1]);
              iconFeaturesPol.push(iconFeaturePol);
              allFeatures.push(iconFeaturePol);

              iconFeaturePol.setStyle(featureStyleBl);


            }
            // Else we assume geographic extent is a point, and create a pin feature
            else {
              geom = new ol.geom.Point(ol.proj.transform([extracted_info[i12][3][1], extracted_info[i12][3][0]], 'EPSG:4326', prj));
              //Define pin features
              var iconFeaturePin = new ol.Feature({
                url: extracted_info[i12][0],
                id: extracted_info[i12][1],
                geometry: geom,
                extent: [extracted_info[i12][2][0], extracted_info[i12][2][1], extracted_info[i12][2][2], extracted_info[i12][2][3]],
                latlon: extracted_info[i12][3],
                title: extracted_info[i12][4],
                abs: extracted_info[i12][5],
                time: [extracted_info[i12][6][0], extracted_info[i12][6][1]],
                thumb: extracted_info[i12][7],
                related_info: [extracted_info[i12][8][0], extracted_info[i12][8][1]],
                iso_keys_coll_act: extracted_info[i12][9],
                info_status: extracted_info[i12][10],
                data_center: extracted_info[i12][11],
                actions: extracted_info[i12][12],
                contacts: extracted_info[i12][13],
                constraints: extracted_info[i12][14],
                core: extracted_info[i12][15],
                feature_type: extracted_info[i12][16],
                name: "Pin Feature",
              });
              iconFeaturePin.setId(extracted_info[i12][1]);
              iconFeaturesPin.push(iconFeaturePin);
              allFeatures.push(iconFeaturePin);

              if ((extracted_info[i12][2][0] !== extracted_info[i12][2][1]) || (extracted_info[i12][2][2] !== extracted_info[i12][2][3])) {
                iconFeaturePin.setStyle(iconStyleBl);
              } else {
                iconFeaturePin.setStyle(iconStyleBk);
              }
            }

          }


          //create a vector source with all points
          var vectorSourcePol = new ol.source.Vector({
            features: iconFeaturesPol,
            name: 'polygonSource',
            //projection: proj,
          });

          //create a vector layer with all points from the vector source and pins
          featureLayers['polygons'] = new ol.layer.Vector({
            title: 'Polygons',
            name: 'polygonsLayer',
            //projection: prj,
            source: vectorSourcePol,
          });



          //create a vector source with all points
          var vectorSourcePin = new ol.source.Vector({
            features: iconFeaturesPin,
            name: 'pinsSource',
          });
          featureLayers['pins'] = new ol.layer.Vector({
            title: 'Pins',
            name: 'pinsLayer',
            source: vectorSourcePin,
            //projection: prj,
            //style: iconStyle,
          });
          //create a vector layer with all points from the vector source and pins



          //Fit to extent of features
          var featuresExtent = new ol.extent.createEmpty();
          allFeatures.forEach(function(feature) {
            featuresExtent = new ol.extent.extend(featuresExtent, feature.getGeometry().getExtent());
          });
          //var maxExt = extent.getExtent();
          console.log("Adding feature layers to map");
          map.addLayer(featureLayers['polygons']);
          map.addLayer(featureLayers['pins']);

          return featuresExtent
        }

        //initialize features
        console.log("Building features with projection: " + selected_proj);
        var featuresExtent = buildFeatures(projObjectforCode[selected_proj].projection);
        map.getView().setCenter(ol.extent.getCenter(featuresExtent));
        map.getView().fit(featuresExtent);
        map.getView().setZoom(map.getView().getZoom() - 0.3);
        // display clickable ID in tooltip
        //console.log('calling id_tooltip');
        //id_tooltip()

        //Register the tooltip and prosuct select actions
        id_tooltip_new()
        id_tooltip_h()
        //createOverViewMap(selected_proj)

        //Function to zoom to extent of all features:
        function zoomToProductsExtent() {
          console.log("Zoom back to features extent");
          map.getLayers().forEach(function(element, index, array) {
            if (element.get('title') === 'pins') {
              console.log("Set pins layer visible");
              element.setVisible(true);
              element.getSource().refresh();
              if (element.get('title') === 'polygons') {}
              console.log("Set polygon layer visible");
              element.setVisible(true);
              element.getSource().refresh();
            }
          });
          map.getView().setCenter(ol.extent.getCenter(featuresExtent));
          map.getView().fit(featuresExtent);
          map.getView().setZoom(map.getView().getZoom() - 0.3);


        }


        /* Create a bbox vector layer of the current bboxFilter in use */
        // recreate drawings when fields are filled
        function getActiveBbox(selected_proj) {
          if (tllat !== null && tllon !== null && brlat !== null && brlon !== null) {
            var topLeft = [Number(tllon), Number(tllat)];
            var bottomRight = [Number(brlon), Number(brlat)];
            if (bottomRight[0] < topLeft[0]) {
              topLeft[0] -= 360;
            }

            var points = [
              [
                ol.proj.transform(topLeft, 'EPSG:4326', proj),
                ol.proj.transform([bottomRight[0], topLeft[1]], 'EPSG:4326', proj),
                ol.proj.transform(bottomRight, 'EPSG:4326', proj),
                ol.proj.transform([topLeft[0], bottomRight[1]], 'EPSG:4326', proj),
              ]
            ];


            //Create bbox draw style
            var bboxStyle = new ol.style.Style({
              stroke: new ol.style.Stroke({
                color: 'blue',
                width: 1,
              }),
              fill: new ol.style.Fill({
                color: 'rgba(0, 0, 255, 0.1)',
              }),
            });
            // Create bbox source
            var bboxSource = new ol.source.Vector({
              projection: selected_proj,
            });
            console.log('Created bboxSource');

            //Create bbox layer
            var bboxLayer = new ol.layer.Vector({
              source: bboxSource,
              style: bboxStyle,
              visible: true,
              title: 'CurrentBbox',
              projection: selected_proj,
            });
            console.log('Created bboxLayer');
            //overviewMapControl.addLayer(bboxLayer);

            var bboxGeom = new ol.geom.Polygon(points);

            //Create a feature with polygon from current bbox
            var bboxFeature = new ol.Feature(bboxGeom);
            bboxFeature.setStyle(bboxStyle);
            bboxSource.addFeature(bboxFeature);
            console.log('Created bboxFeature');

            return bboxLayer;
          }
        }

        //Adding configured additional layers
        if (additional_layers) {
          console.log("Adding additional layers");
          addExtraLayers(selected_proj);
        }


        //Mouseposition lat lon
        var mousePositionControl = new ol.control.MousePosition({
          coordinateFormat: function(co) {
            return ol.coordinate.format(co, template = 'lon: {x}, lat: {y}', 2);
          },
          projection: 'EPSG:4326',
        });
        map.addControl(mousePositionControl);

        //Zoom to extent
        var zoomToExtentControl = new ol.control.ZoomToExtent({
          extent: featuresExtent,
        });
        map.addControl(zoomToExtentControl);


        //Add LayerSwitcher control
        var layerSwitcher = new ol.control.LayerSwitcher({
          tipLabel: 'Legend', // Optional label for button
          groupSelectStyle: 'children' // Can be 'children' [default], 'group' or 'none'
        });
        map.addControl(layerSwitcher);
        /** WMS LAYERS - Visualize all **/
        //Loop over the extracted info, and check how many wms resources we have
        var wmsProducts = [];
        var wmsProductLayers = [];
        for (var i = 0; i < extracted_info.length; i++) {
          id = extracted_info[i][1];
          wms = extracted_info[i][0][1];
          //if(debug) {console.log("id: "+id+ ",wms:" +wms)};
          if (wms != null && wms != "") {
            wmsProducts.push(id);
            wmsProductLayers.push(wms);
          }
        }

        // If we have wms datasets in map, show the visualise all button

        //list of olWMALayers to be added and rendered
        var wmsLayers = [];
        if (wmsProducts.length > 0) {
          $('#vizAllButton').css('display', 'block');
          $('#vizAllButton').append().text('Viusalise all WMS resources in Map');
          $('#vizAllButton').on("click", function(e) {
            console.log("Visialise all wms click event");
            console.log("current projection" + selected_proj);
            // clear pins and polygons
            //map.getLayers().remove(layer['polygons']);
            //map.getLayers().remove(layer['pins']);
            //Hide Pins and polygons
            map.getLayers().forEach(function(element, index, array) {
              if (element.get('title') === 'pins') {
                element.setVisible(false);
              }
              if (element.get('title') === 'polygons') {
                element.setVisible(false);
              }
            })

            //Loop over the wmsLayers and render them on map.
            for (let i = 0; i < wmsProductLayers.length; i++) {
              console.log(i + " - " + wmsProducts[i]);
              //alert(wmsProducts[i]);

              wmsLayers.push(
                //map.addLayer(
                new ol.layer.Tile({
                  title: wmsProducts[i],
                  visible: true,
                  projections: projObjectforCode[proj].projection,
                  source: new ol.source.TileWMS( /** @type {olx.source.TileWMSOptions} */ ({
                    url: wmsProductLayers[i],
                    //projection: projObjectforCode[proj].projection,
                    params: {
                      'LAYERS': 'Composites',
                      //'LAYERS': 'WMS',
                      //'FORMAT': 'image/jpeg',
                      'TILE': true,
                      'TRANSPARENT': true,
                    },
                    crossOrigin: 'anonymous',
                  })),
                }),
                new ol.layer.Tile({
                  title: wmsProducts[i],
                  visible: true,
                  projections: projObjectforCode[proj].projection,
                  source: new ol.source.TileWMS( /** @type {olx.source.TileWMSOptions} */ ({
                    url: wmsProductLayers[i],
                    //projection: projObjectforCode[proj].projection,
                    params: {
                      'LAYERS': 'area',
                      //'LAYERS': 'WMS',
                      //'FORMAT': 'image/jpeg',
                      'TILE': true,
                      'TRANSPARENT': true,
                    },
                    crossOrigin: 'anonymous',
                  })),
                }),
              );

            }
            map.getLayers().extend(wmsLayers);


          });
          //id_tooltip_h()
        }


        /* Draw bounding box filter event */
        // Search bbox filter
        $('#bboxButton').click(function() {
          console.log('Creating bbox filter with projection: ' + proj);
          console.log(featureLayers);
          // clear pins and polygons
          //layers = map.getLayers();
          featureLayers['pins'].setVisible(false);
          featureLayers['polygons'].setVisible(false);
/*
          map.getLayers().forEach(function(element, index, array) {
            if (element.get('title') === 'pins') {
              element.setVisible(false);
            }
            if (element.get('title') === 'polygons') {
              element.setVisible(false);
            }
          })
          map.getLayers().remove(wmsLayers);
*/
          //Unset the current product overlays and mouse position control
          map.un('singleclick', getProductInfo);

          //remove mouse position control
          map.removeControl(mousePositionControl);
          //Remove overlay
          map.removeOverlay(overlayh);


          //New draw Mouseposition control
          var mousePositionControl = new ol.control.MousePosition({
            coordinateFormat: function(co) {
              return ol.coordinate.format(co, template = 'lon: {x}, lat: {y}', 2);
            },
            projection: 'EPSG:4326',
          });
          map.addControl(mousePositionControl);

          // Build the draw of bbox
          build_draw(selected_proj)

        });

        //Draw bbox function
        function build_draw(selected_proj) {

          // Add drawing vector source
          var drawingSource = new ol.source.Vector({
            projection: selected_proj
          });
          //Add drawing layer
          var drawingLayer = new ol.layer.Vector({
            source: drawingSource,
            title: 'draw',
            projection: selected_proj
          });
          map.addLayer(drawingLayer);

          var geometryFunction = function(coordinates, geometry, projection) {
            var start = coordinates[0]; //x,y
            var end = coordinates[1];

            // transform in latlon
            var start_ll = ol.proj.transform(start, projection, 'EPSG:4326'); //lon,lat
            var end_ll = ol.proj.transform(end, projection, 'EPSG:4326');
            var left_ll = [start_ll[0], end_ll[1]];
            var right_ll = [end_ll[0], start_ll[1]];

            var left = ol.proj.transform(left_ll, 'EPSG:4326', projection);
            var right = ol.proj.transform(right_ll, 'EPSG:4326', projection);


            const boxCoordinates = [
              [
                start, left, end, right, start,
              ],
            ];

            if (geometry) {
              geometry.setCoordinates(boxCoordinates);
            } else {
              geometry = new ol.geom.Polygon(boxCoordinates);
            }
            return geometry;
          }
          var draw; // global so we can remove it later
          draw = new ol.interaction.Draw({
            source: drawingSource,
            type: 'LineString',
            //geometryFunction: ol.interaction.Draw.createBox(),
            geometryFunction: geometryFunction,
            maxPoints: 2
          });


          var mapFilter = drupalSettings.metsis_search_map_block.mapFilter;

          draw.on('drawstart', function(e) {
            drawingSource.clear();
          });

          draw.on('drawend', function(e) {

            coords = e.feature.getGeometry().getCoordinates();
            var a = ol.proj.transform(coords[0][0], map.getView().getProjection().getCode(), 'EPSG:4326');
            var b = ol.proj.transform(coords[0][1], map.getView().getProjection().getCode(), 'EPSG:4326');
            var c = ol.proj.transform(coords[0][2], map.getView().getProjection().getCode(), 'EPSG:4326');
            var d = ol.proj.transform(coords[0][3], map.getView().getProjection().getCode(), 'EPSG:4326');
            var e = ol.proj.transform(coords[0][4], map.getView().getProjection().getCode(), 'EPSG:4326');
            var topLeft = [Math.min(a[0], c[0]), Math.max(a[1], c[1])];
            var bottomRight = [Math.max(a[0], c[0]), Math.min(a[1], c[1])];

            if (topLeft[0] < -180) {
              topLeft[0] += 360;
            } else if (topLeft[0] > 180) {
              topLeft[0] -= 360;
            }
            if (bottomRight[0] < -180) {
              bottomRight[0] += 360;
            } else if (bottomRight[0] > 180) {
              bottomRight[0] -= 360;
            }
            if (topLeft[0] < 0 && bottomRight[0] > 0 && bottomRight[0] - topLeft[0] > 180) {
              var topLeftCopy = topLeft[0];
              topLeft[0] = bottomRight[0];
              bottomRight[0] = topLeftCopy;
            }


            /* Send the bboundingbox back to drupal metsis search controller to add the current boundingbox filter to the search query */
            var myurl = '/metsis/search/map?tllat=' + topLeft[1] + '&tllon=' + topLeft[0] + '&brlat=' + bottomRight[1] + '&brlon=' + bottomRight[0] + '&proj=' + selected_proj;
            console.log('calling controller url: ' + myurl);
            data = Drupal.ajax({
              url: myurl,
              async: false
            }).execute();

            //Do something after ajax call are complete
            $(document).ajaxComplete(function(event, xhr, settings) {
              console.log('ajax complete:' + drupalSettings.metsis_search_map_block.bboxFilter);
              var bboxFilter = drupalSettings.metsis_search_map_block.bboxFilter;
              $('.current-bbox-select').replaceWith(bboxFilter);

              var tllat = drupalSettings.metsis_search_map_block.tllat;
              var tllon = drupalSettings.metsis_search_map_block.tllon;
              var brlat = drupalSettings.metsis_search_map_block.brlat;
              var brlon = drupalSettings.metsis_search_map_block.brlon;

            });



          });
          console.log('Adding draw bbox interaction');
          map.addInteraction(draw);
          /*
          tllat = drupalSettings.metsis_search_map_block.tllat;
          tllon = drupalSettings.metsis_search_map_block.tllon;
          brlat = drupalSettings.metsis_search_map_block.brlat;
          brlon = drupalSettings.metsis_search_map_block.brlon;
          */
          console.log('tllat before draw existing filter' + tllat);

          // two proj
        }

        //Add extra layers function
        function addExtraLayers(proj) {

          document.getElementById("droplayers").style.display = "none";

          if (additional_layers && (proj == 'EPSG:4326' || proj == 'EPSG:32661')) {
            $('#droplayers').appendTo(
              $('.ol-overlaycontainer-stopevent')
            );
            featureLayers['europaveg'] = new ol.layer.Tile({
              title: 'europaveg',
              source: new ol.source.TileWMS({
                url: 'https://openwms.statkart.no/skwms1/wms.vegnett?',
                params: {
                  'LAYERS': 'europaveg',
                  'TRANSPARENT': 'true',
                  'VERSION': '1.3.0',
                  'FORMAT': 'image/png',
                  'CRS': proj
                },
                crossOrigin: 'anonymous'
              })
            });

            featureLayers['riksveg'] = new ol.layer.Tile({
              title: 'riksveg',
              displayInLayerSwitcher: true,
              source: new ol.source.TileWMS({
                url: 'https://openwms.statkart.no/skwms1/wms.vegnett?',
                params: {
                  'LAYERS': 'riksveg',
                  'TRANSPARENT': 'true',
                  'VERSION': '1.3.0',
                  'FORMAT': 'image/png',
                  'CRS': proj
                },
                crossOrigin: 'anonymous'
              })
            });

            featureLayers['fylkesveg'] = new ol.layer.Tile({
              title: 'fylkesveg',
              source: new ol.source.TileWMS({
                url: 'https://openwms.statkart.no/skwms1/wms.vegnett?',
                params: {
                  'LAYERS': 'fylkesveg',
                  'TRANSPARENT': 'true',
                  'VERSION': '1.3.0',
                  'FORMAT': 'image/png',
                  'CRS': proj
                },
                crossOrigin: 'anonymous'
              })
            });


            for (var i = layers_list.length; i--;) {
              var ald = document.getElementById("lrslist").children; //list of li
              if (ald[i].children[0].checked) {
                selectedLayer = ald[i].children[0].value;
                map.addLayer(featureLayers[selectedLayer]);
              }
              ald[i].children[0].onclick = function select_extralayer() {
                if (this.checked) {
                  selectedLayer = this.value;
                  map.addLayer(featureLayers[selectedLayer]);
                } else {
                  selectedLayer = this.value;
                  map.removeLayer(featureLayers[selectedLayer]);
                }
              }
            }

            document.getElementById("droplayers").style.display = "inline";
          }
        }

      });
    },
  };

})(jQuery, Drupal, drupalSettings);
