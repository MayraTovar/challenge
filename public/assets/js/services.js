
var getWeeks = function(){
  $.ajax({
    url: "../server.php",
    success: function(response){
      response.reverse();
      for(var i=0; i<response.length; i++){
        response[i] = parseInt(response[i]);
        $('#select-weeks').append('<option>'+ response[i]+'</option>')
      }
       var max = Math.max.apply(null, response);
       $.ajax({
          url: "../server.php?filter=week&value="+max,
          success: function(response){
            for(var i=0; i<response.length; i++){
              $('#table-results').append(
                $('<tr>').append(
                '<td>' +   response[i].HomeTeamKey + ' '+ response[i].HomeTeamName+' '+ '<span style="color:#E3E3E3;">'+response[i].home_score+'</span></td>' +
                '<td>' +   response[i].AwayTeamKey + ' '+ response[i].AwayTeamName+' '+ '<span style="color:#E3E3E3;">'+response[i].away_score+'</span></td>' +
                '<td>' +   response[i].date + '</td>'+
                '<td>' +   response[i].location + '</td>'+
                '<td>' +   response[i].status + '</td>'+
                '<td>' +   response[i].week + '</td>'
                  // $('<td>',{text:response[i].HomeTeamKey + ' '+ response[i].HomeTeamName+' '+ ''+response[i].home_score+'</h2>'}),
                  // $('<td>',{text:response[i].AwayTeamKey}),
                  // $('<td>',{text:response[i].AwayTeamName}),
                  // $('<td>',{text:response[i].away_score}),
                  // $('<td>',{text:response[i].AwayTeamName})


                )
              );
            }

          },
          dataType: "json"
        })
    },
    dataType: "json"
  })
};

$( "#select-weeks" ).change(function() {
  $('#table-results tbody').find('tr').remove().end();
  $.ajax({
     url: "../server.php?filter=week&value="+$(this).val(),
     success: function(response){
       for(var i=0; i<response.length; i++){
         $('#table-results').append(
           $('<tr>').append(
           '<td>' +   response[i].HomeTeamKey + ' '+ response[i].HomeTeamName+' '+ '<span style="color:#E3E3E3;">'+response[i].home_score+'</span></td>' +
           '<td>' +   response[i].AwayTeamKey + ' '+ response[i].AwayTeamName+' '+ '<span style="color:#E3E3E3;">'+response[i].away_score+'</span></td>' +
           '<td>' +   response[i].date + '</td>'+
           '<td>' +   response[i].location + '</td>'+
           '<td>' +   response[i].status + '</td>'+
           '<td>' +   response[i].week + '</td>'
             // $('<td>',{text:response[i].HomeTeamKey + ' '+ response[i].HomeTeamName+' '+ ''+response[i].home_score+'</h2>'}),
             // $('<td>',{text:response[i].AwayTeamKey}),
             // $('<td>',{text:response[i].AwayTeamName}),
             // $('<td>',{text:response[i].away_score}),
             // $('<td>',{text:response[i].AwayTeamName})


           )
         );
       }

     },
     dataType: "json"
   })
});


// var getElements = function(week){
//   $.ajax({
//     url: "/server.php?filter=week&value="+week,
//     success: function(response){
//       for(var i=0; i<response.length; i++){
//         $('#table-results').append(
//           $('<tr>').append(
//       '<td>' +   response[i].HomeTeamKey + ' '+ response[i].HomeTeamName+' '+ '<span style="color:#E3E3E3;">'+response[i].home_score+'</span></td>' +
//       '<td>' +   response[i].AwayTeamKey + ' '+ response[i].AwayTeamName+' '+ '<span style="color:#E3E3E3;">'+response[i].away_score+'</span></td>' +
//       '<td>' +   response[i].date + '</td>'+
//       '<td>' +   response[i].location + '</td>'+
//       '<td>' +   response[i].status + '</td>'+
//       '<td>' +   response[i].week + '</td>'
//             // $('<td>',{text:response[i].HomeTeamKey + ' '+ response[i].HomeTeamName+' '+ ''+response[i].home_score+'</h2>'}),
//             // $('<td>',{text:response[i].AwayTeamKey}),
//             // $('<td>',{text:response[i].AwayTeamName}),
//             // $('<td>',{text:response[i].away_score}),
//             // $('<td>',{text:response[i].AwayTeamName})
//
//
//           )
//         );
//       }
//
//     },
//     dataType: "json"
//   })
// };
//
// getElements(week);
getWeeks();
