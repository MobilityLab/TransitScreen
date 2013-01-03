var screenversion = 0;  // To be written by php
var lastupdate = 0;     // To be updated by json
//var blankout = 100;   // After losing a connection, the screen
                        // will autodecrement each prediction for
                        // this number of minutes.
var buslimit = 99;      // Bus limit per block
var firstload = true;
var queryurl = '../../update/json/' + screen_id; //e.g. http://localhost/index.php/update/json/1

var blocks = new Array();    // Array of stop and arrival data

function generate_blocks() {
  // loop through local data and create templates.
  for(var key in blocks){
    var output = '';
    var vcount = 0;

    classname_base = translate_class_name(blocks[key].type);
    if(classname_base == 'bus'){
      containerclass = 'bus_stop_container';
    }
    else {
      containerclass = classname_base + '_container';
    }

    // For bus or rail, output data this way
    if(blocks[key].type == 'subway' || blocks[key].type == 'bus'){      

      output += '<div class="' + containerclass + '">';
      output += ' <div class="' + classname_base + '_location">';
      output += '   <div id="' + classname_base + '_logo"></div>';
      output += '   <h2>' + blocks[key].name + '</h2>';
      output += ' </div>';
      output += ' <table id="' + classname_base + '_table">';

      $.each(blocks[key].vehicles, function(v,vehicle){

        var vout = '';

        if(vehicle.predictions.length > 0){
          var subsequent = '';
          var class_suffix = get_suffix(vehicle.route, vehicle.agency);

          if(blocks[key].type == 'subway') {
            railsuffix = '_dark transparent';
          }
          else  {
            railsuffix = '';
          }

          if(class_suffix == 'ART'){
            logoclass = ' bus_line_art_logo';
          }
          else if(class_suffix == 'Circulator'){
            logoclass = ' bus_line_circ_logo';
            vehicle.route = '&nbsp;';
          }
          else if(class_suffix == 'pgc'){
            logoclass = ' bus_line_pgc_logo';
			vehicle.route = '&nbsp;';
          }
          else {
            logoclass = '';
          }

          vout += '   <tr class="' + classname_base + '_table_module" id="block-' + blocks[key].id + '-vehicle-' + v + '">';
          vout += '     <td class="' + classname_base + '_table_line">';
          vout += '       <div class="' + classname_base + '_line ' + classname_base + '_line_' + class_suffix + logoclass + '">';
          vout += '         <h3>' + vehicle.route + '</h3>';
          vout += '       </div>';
          vout += '     </td>';
          vout += '     <td class="' + classname_base + '_table_destination ' + classname_base + '_line_' + class_suffix + railsuffix + '">';
          vout += split_destination(vehicle.agency, vehicle.destination);
          vout += '     </td>';
          $.each(vehicle.predictions, function(p, prediction) {
            // For the first prediction
            if(p == 0) {
              vout += '     <td class="' + classname_base + '_table_time">';
              vout += '       <h3>' + prediction + '</h3>';

              if(blocks[key].type == 'bus') {
                vout += '       <span class="bus_min">MINUTE' + pluralize(prediction) + '</span>';
              }
                 
              if(blocks[key].type == 'subway') {
                vout += '       <h4>MINUTE' + pluralize(prediction) + '</h4>';
              }

              vout += '     </td>';
            }
            
            if(p > 0 && p < 3) {
              subsequent += '       <h4>' + prediction + '</h4>';
            }

          });
          
          vout += '     <td class="' + classname_base + '_table_upcoming">' + subsequent + '</td>';
          vout += '   </tr>';
          
          //console.log(blocks[key].name + ': ' + vcount + ' / ' + buslimit);

          if(blocks[key].type == 'bus' && (vcount >= buslimit)){
            vout = '';
          }

          output += vout;

          vcount++;

        }
      });
      output += '   </table>';
      output += '</div>'; 
    }

    // For CaBi, output data this way
    if(blocks[key].type == 'cabi'){

      var bikelist = '';
      
      // For each station, assemble the table row
      $.each(blocks[key].stations, function(c, cabistation) {
        bikelist += '   <tr class="cabi_data">';
        bikelist += '     <td class="pie"><img src="https://chart.googleapis.com/chart?cht=p&chs=100x80&chd=t:' + cabistation.bikes + ',' + cabistation.docks + '&chco=ff0000|b3b3b3&chf=bg,s,000000&chp=1.58" /></td>';
        bikelist += '     <td class="cabi_location">';
        bikelist += '       <span class="cabi_dock_location">' + cabistation.stop_name + '</span>';
        bikelist += '     </td>';
        bikelist += '     <td>';
        bikelist += '       <h3 class="cabi_bikes">' + cabistation.bikes + '</h3>';
        bikelist += '     </td>';
        bikelist += '     <td>';
        bikelist += '       <h3 class="cabi_docks">' + cabistation.docks + '</h3>';
        bikelist += '     </td>';
        bikelist += '   </tr>';
      });

      output += '<div id="block-' + blocks[key].id + '" class="' + containerclass + '">';
      output += ' <table id="' + classname_base + '_table">';
      output += '   <tr class="' + classname_base + '_header">';
      output += '     <td colspan="2">';
      output += '       <span class="cabi_icon">&nbsp;</span>';
      output += '     </td>';     
      output += '     <td class="bikes">';
      output += '       <h4>BIKES</h4>';
      output += '     </td>';
      output += '     <td class="docks">';
      output += '       <h4>DOCKS</h4>';
      output += '     </td>';
      output += '   </tr>';
      output += bikelist;
      output += ' </table>';
      output += '</div>';      
    }

    if(blocks[key].type == 'custom'){
      output += '<div id="block-' + blocks[key].id + '" class="' + containerclass + '">';
      output +=   blocks[key].custom_body;
      output += '</div>';   
    }
    
    if('vehicles' in blocks[key] || blocks[key].type == 'cabi' || blocks[key].type == 'custom'){
      if($('#block-' + blocks[key].id).length > 0){
        $('#block-' + blocks[key].id).html(output);
      }
      else {
        //$("#results").append('<div class="block" id="block-' + blocks[key].id + '">' + output + '</div>');
        $("#col-" + blocks[key].column).append('<div class="block" id="block-' + blocks[key].id + '" order="' + blocks[key].order + '">' + output + '</div>');
      }
    }
    else {
      $('#block-' + blocks[key].id).empty();
    }
  }
  reorder_blocks(); 
}

function reorder_blocks() {
  $.each($('.col'), function(c,colm){
    var mylist = $('#col-' + (c+1));
    var listitems = mylist.children('.block').get();

    listitems.sort(function(a, b) {
      var compA = $(a).attr('order');
      var compB = $(b).attr('order');
      return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
    })
    $.each(listitems, function(idx, itm) { mylist.append(itm); });
  });
}


function translate_class_name(jsonname){
  switch(jsonname) {
    case 'bus':
      return 'bus';
    case 'subway':
    case 'metro':
      return 'metro';
    case 'cabi':
      return 'cabi';
    case 'custom':
      return 'custom';
    default:
      return 'unknown';
  }
}

function split_destination(agency, input){
  if(agency == 'Metrobus'){
    switch(input.substring(0,8)){
      case 'North to':
        direction = '<h3>Northbound to:</h3>';
        break;
      case 'South to':
        direction = '<h3>Southbound to:</h3>';
        break;
      case 'East to ':
        direction = '<h3>Eastbound to:</h3>';
        break;
      case 'West to ':
        direction = '<h3>Westbound to:</h3>';
        break;
    }
    return direction + '<h4>' + input.substring(8) + '</h4>';
  }
  else if(agency == 'metrorail'){
      return '<h3>' + input + '</h3>';
  }
  else {
    return '<h4>' + input + '</h4>';
  }
}

function get_suffix(route, agency){
  if(agency == 'metrorail'){
    switch(route) {
      case 'RD':
        return 'red';
      case 'OR':
        return 'orange';
      case 'YL':
        return 'yellow';
      case 'GR':
        return 'green';
      case 'BL':
        return 'blue';
    }
    return route;
  }

  if(agency == 'Metrobus') {
    return 'wmata';
  }
  
  return agency;  
}

function pluralize(num) {
  if(num != 1){
    return 'S';
  }
  return '';
}

function refresh_data() {
  var now = Math.round(new Date().getTime() / 1000);  
  
  // query the server for the new data
  //$.getJSON("http://localhost/index.php/update/json/" + screen_id,function(json){
  //$.getJSON("../../update/json/" + screen_id,function(json){
  //$.getJSON("http://localhost/scripts/json.js",function(json){
  $.getJSON(queryurl,function(json){
    if(json.screen_version > screenversion) {
      
      $.get(document.URL, function(newpage){
        console.log(newpage);
        newpage = newpage.replace('<html>','');
        newpage = newpage.replace('</html>','');
        $('html').html(newpage);
      })        
      .error(function() { console.log("error"); })
      //window.location.reload(); 
    }
    lastupdate = now;
    //blocks.updated = now; // Set the updated time for the local dataset
    
    $('#loading-box').remove();
    
    // For each stop ...
    $.each(json.stops,function(i,stop){
      thisid = stop.id;
      blocks[thisid] = stop; // Update each block with new data.
      blocks[thisid].updated = now;
    });

    // Call the function to create or recreate the blocks based
    // on the updated data.
    generate_blocks();
    
  })
  .error(function() { // This executes if the script cannot get the updated data.
    //alert("error");
  });

  // write the new data to the local data store
  // add/update a "last updated" property to each object
}

/*
function time_tracker(id, lastcheck, iteration) {
  // each minute, automatically decrement each prediction
  // in the local data.  
  var removeid = new Array();
  var removevehicle = new Array();

  var now = Math.round(new Date().getTime() / 1000);

  if((now - blocks[id].updated) > 5){
    console.log('Minute mark');
    blocks[id].updated = now;

    $.each(blocks[id].vehicles, function(v, vehicle) {      
      removevehicle = new Array();
      removeid = new Array();
      $.each(vehicle.predictions, function(p, prediction) {

        if(prediction > 0){
          blocks[id].vehicles[v].predictions[p]--;          
        }
        else {
          // Add the id to the array of ids whose elements
          // should be removed.
          removeid.push(p);
        }
      });

      removeid.reverse();

      $.each(removeid, function(r, item) {
        console.log('Remove ' + item);
        blocks[id].vehicles[v].predictions.splice(item,1);
      });

      // If the route has no more predictions, remove it.
      if(blocks[id].vehicles[v].predictions.length == 0){
        console.log('Remove route ' + blocks[id].vehicles[v].route + ' ' + blocks[id].vehicles[v].destination + ' id: ' + v + ', ' + '#block-' + id + '-vehicle-' + v);
        $('#block-' + id + '-vehicle-' + v).empty();
        removevehicle.push(v);
        //blocks[id].vehicles.splice(v,1);
      }      
    });

    removevehicle.reverse();
    console.log('Cleaning up the buses.');
    $.each(removevehicle, function(rv, bus) {
      console.log('Now removing bus ' + bus);
      blocks[id].vehicles.splice(bus,1);
    });
  }  
}
*/

// Do this as the initial load
$(document).ready(function () {
  $.getJSON(queryurl,function(json){
    if(screenversion == 0){
      screenversion = json.screen_version;
    }
    refresh_data();
  })
  .error(function() {
  });
});

// This triggers the data update
$(document).everyTime(45000, function(){
  
  var url = queryurl + '?' +  new Date().getTime();

  $.getJSON(url,function(json){
    // If the script can get the file, do the following...

      // Regenerate the templates from the presented structure
      if(screenversion == 0){
        screenversion = json.screen_version;
      }

      if(json.sleep == false) {
        refresh_data();
      }
      else {                // If the screen should sleep
        $('.col').empty();  // clear everything out.
      }
  })
  .error(function() { // This executes if the script cannot get the updated data.
    // Update the block rendering since the auto-decrementer
    // will kick in.
    //generate_blocks();
  });

});

// Run this timer to autodecrement
/*
$(document).everyTime(5000, function(){
  var now = Math.round(new Date().getTime() / 1000);

  for(var key in blocks){
    // If the block was not updated set the notify the auto-
    // decrement function.

    if((now - blocks[key].updated) >= (blankout)){
      blocks.splice(key,1);
      $('#block-' + key).empty();
      continue;
    }
    if((now - blocks[key].updated) > 4){      
      time_tracker(key, now, 0);
    }
  }
});*/

