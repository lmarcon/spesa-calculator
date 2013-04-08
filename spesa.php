<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title>LdP spesa calculator 0.1</title>
  <style type='text/css'>
  body {
	font: 10pt Verdana sans-serif;
  }

  a {
	cursor: pointer;
	cursor: hand;
	text-decoration: underline;
  }

  a:hover {
	color: red;
  }

  input {
	text-align: right;
  }

  .odd{
	background: #dedede;
  }

  .prezzo, .desc {
/*	float: left;*/
  }
  </style>
  <script language='javascript' type='text/javascript' src='jquery-1.2.6.js'></script>
  <script language='javascript' type='text/javascript' src='jquery.validation.js'></script>
  <script language='javascript' type='text/javascript'>
	var lcolors = ['#BFFFBF','#FFEABF','#C6CAFF','#FFBFBF','#E4BFFF','#FFFDBF','#E6C273'];
	var dcolors = ['#80FF80','#FFD680','#8D95FF','#FF8080','#CA80FF','#FFFB80','#BF9330'];

	 function ldpLength( a )
	 {
		 c = 0;
		 for( var i in a )
			c++;

		 return c;
	 }

	 function delElmByIndex( arr, idx )
	 {
		 var ret = new Array();
		 for( var i in arr )
			 if( i != idx )
				 ret[i] = arr[i];

		 return ret;
	 }
	 
	 function fixAtTwo(anynum) 
	 {
		var numStr=anynum.toString();
		var decPos = numStr.indexOf(".");	
		if ( decPos == -1 )
			return numStr + ".00";
		else
		{
			// add two zeros to get at least two digits at the 
			// end of the string in case it ends at the decimal
			numStr += "00";	
			return numStr.substr(0,decPos+3);
		}
	}

  // people è un array associativo nome_persona:prezzo_da_pagare, inizializzato a 0.
  var people = new Array(), rows = 0;
  function addLine( d, pr )
  {
	  d = !d ? '' : d;
	  pr = !pr ? '' : pr;

	  var cl = '';
	  if( !(rows % 2 ) )
		  cl = ' odd';

	  var row = "<div class='riga"+cl+"' id='_"+rows+"'><div id='"+rows+"'><input type='text' class='desc' id='desc_"+rows+"' value='"+d+"' /> <input type='text' class='prezzo' size='4' id='price_"+rows+"' onkeyup='ricalcola()' value='"+pr+"' />&nbsp;&nbsp;";
	  var idx = 0;
	  for( var i in people )
	  {
		  row += getPersonBox( rows, i, idx );
		  idx++;
	  }

	  row += "</div></div>";

	  $( '#center' ).append( row );
	  $( '#desc_'+rows ).validation({ add: " _-", onError: function(){ this.value = ''; } });
	  $( '#price_'+rows ).validation({ type: "int", add: ".", onError: function(){ this.value = 0; } });
	  rows++;
  }

  function delLine( id )
  {
	  // TODO ma serve?
	  //rows--;
  }

  function getPersonBox( r, p, idx )
  {
	  var colors = !(r % 2) ? dcolors : lcolors;
	  return "<span id='sp_"+r+"_"+p+"' style='padding:2px;background: "+colors[idx%colors.length]+";'>"+p+" <input type='checkbox' class='check' id='"+r+"_"+p+"' value='"+r+"_"+p+"' onclick='ricalcola()' /> </span>";
  }

  function delPerson( p )
  {
	  var row;
	  $( '#hstr_'+p ).remove();
	  $( '.prezzo' ).each( function( i ){
		  for( var k in people )
			  if( k == p )
			  {
				  row = this.id.split( '_' );
				  $( '#sp_'+row[1]+'_'+k ).remove();
			  }
	  });
	  people = delElmByIndex( people, p );
	  if( ldpLength( people ) == 0 )
	  {
		  $( '.riga' ).remove();
		  $( '#addl' ).hide();
	  }

	  ricalcola();
  }

  function addPerson()
  {
	  var p = prompt( 'Nome persona?' );
	  if( !p )
		  return;

	  addP( p );
  }

  // p è il nome della persona, h sono quanti soldi ha messo
  function addP( p, h )
  {
	  people[p] = 0;
	  h = !h ? '' : h;

	  // per ogni linea aggiungi una persona
	  $( '#addl' ).show();
	  for( i = 0; i < rows; i++ )
		  $( '#'+i ).append( getPersonBox( i, p ) );

	  $( '#people' ).append( "<span id='hstr_"+p+"'><input type='text' id='hamesso_"+p+"' size='4' value='"+h+"' onkeyup='ricalcola()' /> euro messi da <b>"+p+"</b> <a style='font-size: 80%;color:red;' onclick='delPerson(\""+p+"\")'><img src='del.gif' alt='[elimina]' border='0' /></a><br /></span>" );
	  $( '#hamesso_'+p ).validation({ type: "int", add: ".", onError: function(){ this.value = 0; } });
  }

  // la funzione principale che calcola tutto.
  // ogni valore della riga viene diviso per il numero di persone assegnate
  // il valore ottenuto viene sommato ai subtotali di ogni persona.
  // dai subtotali di ogni persona viene sottratta la cifra iniziale messa dalla persona stessa.
  // se per una riga non è selezionata alcuna persona, questa non viene presa in considerazione nel conteggio.
  function ricalcola()
  {
	  var res = '';
	  var tot = 0;
	  for( var j in people )
		  people[j] = 0;

	  // scorriamo i prezzi
	  $( '.prezzo' ).each( function( i ){
		  var cnt = 0, val = 0;
		  var pers = new Array();
		  if( this.value )
		  {
			  row = this.id.split( '_' );
			  for( k in people )
			  {
				  var tmp = $( '#'+row[1]+'_'+k );
				  if( tmp[0].checked )
				  {
					  pers.push( k );
					  cnt++;
				  }
			  }

			  val = parseFloat( this.value ) / cnt;
			  for( l in pers )
				  people[pers[l]] += val;

			  tot += parseFloat( this.value );
		  }
	  });

	  // al totale sottraiamo i soldi messi all'inizio	  
	  for( i in people )
	  {
		  val = parseFloat( $( '#hamesso_'+i ).val() );
		  if( val )
			people[i] -= val;
		  
		  res += '<b>'+i+'</b>: '+fixAtTwo( people[i] )+'<br />';
	  }

	  res = '<b>Totale:</b> '+fixAtTwo( tot )+' euro.<br /><br />' + res;

	  $( '#results' ).html( res );
  }

  // serve a esportare in un formato furbo (che l'utente si salva su file di testo da solo..
  // avremo 2 oggetti:
  // 1) persone e soldi messi all'inizio {'pippo':3.50,'pluto':4}
  // 2) persone-prezzi {'pippo':1,'pluto':0},1_5.25:{'pippo':1,'pluto':1}
  // 3) prezzi e descrizioni {'0_10.50':'prodotto1,'1_5.25_':'prodotto2'}
  function exprt()
  {
	  var res = '{';
	  var res2 = '@@@@{';
	  var tmp = '';

	  // salviamo le persone
	  for( var i in people )
	  {
		  tmp = $('#hamesso_'+i).val() != '' ? $('#hamesso_'+i).val() : 0;
		  res += "'"+i+"':"+tmp+",";
	  }

	  if( ldpLength( people ) > 0 )
		res = res.substring( 0, res.length - 1 );

	  res += '}@@@@[';

	  var cnt = 0;
	  $( '.prezzo' ).each( function( i ){
		  row = this.id.split( '_' );
		  res2 += "'"+row[1]+'_'+this.value+"':'"+$('#desc_'+row[1]).val()+"',";
		  res += '{';
		  for( k in people )
		  {
			  var tmp = row[1]+'_'+k;
			  if( $( '#'+tmp )[0].checked )
				  res += "'"+tmp+"':1,";
			  else
				  res += "'"+tmp+"':0,";
		  }
		  res = res.substring( 0, res.length - 1 );
		  res += '},';
		  cnt++;
	  });

	  if( cnt > 0 )
	  {
		  res = res.substring( 0, res.length - 1 );
		  res2 = res2.substring( 0, res2.length - 1 );
	  }
	  
	  res2 += '}';
	  res += ']';

	  $( '#imex' ).val( res+res2 );
  }

  // serve a importare dal file di testo.
  // per semplicità, eliminiamo gli upload e facciamo incollare direttamente in un textarea.
  function imprt()
  {
	  // qui dovremmo mettere un controllo affinché non vengano messe variabili 'corrotte'

	  // alert stai per perdere tutti i dati
	  if( ldpLength( people ) > 0 )
		  if( !confirm( 'Attenzione: importando i dati verranno persi quelli attuali. Continuare?' ) )
			  return;

	  emptyEverything();

	  var data = $( '#imex' ).val().split( '@@@@' );

	  // "zucchero sintattico"..
	  people = eval( '('+data[0]+')' ); // var globale!
	  var chks = eval( '('+data[1]+')' );
	  var prices = eval( '('+data[2]+')' );

	  // aggiungiamo le righe delle persone
	  for( var i in people )
		  addP( i, people[i] );

	  var tmp;
	  for( i in prices ) // TODO CHECKME dovrebbero essere in ordine..
	  {
		  tmp = i.split( '_' );
		  addLine( prices[i], tmp[1] );
	  }

	  // settiamo i giusti check
	  for( i in chks )
		  for( var j in chks[i] )
			  if( chks[i][j] == 1 )
				  $( '#'+j )[0].checked = true;

	  ricalcola();
  }

  // restart.
  function emptyEverything()
  {
	  people = new Array();
	  rows = 0;
	  $( '#center' ).html( '' );
	  $( '#people' ).html( '' );
	  $( '#results' ).html( '' );
	  $( '#addl' ).hide();
  }
  </script>
 </head>
 <body>
LdP spesa calculator 0.3 (31 Jul 2008) <a style='font-size: 80%; color: blue;text-decoration:none;border-bottom:1px dotted blue;' onclick='$("#todo").toggle()'>[TODO]</a><br />
<div id='impexp' style='display: none; position: absolute; top: 10px; right: 10px; text-align: center;'>* incolla qui sotto *<br /><textarea id='imex' rows='5' cols='40'></textarea><br />
<input type='button' value='importa' onclick='imprt()' /> <input type='button' value='esporta' onclick='exprt()' /></div>
<div id='todo' style='font-size: 80%;display: none;'><br />
* verificare che non vengano inseriti caratteri strani nella descrizione e nei nomi di persona (ovvero caratteri che facciano casino con import/export)<br />
* fare i nomi colorati così si capiscono meglio ed è tutto più colorato e carino<br />
* check di sicurezza tipo: nuova persona con nome già esistente, ecc. <br />
<br />
* <u>nota</u>: probabilmente non funziona _niente_ con explorer.
</div><br />
<b>STEP 1:</b> <a nohref onclick='addPerson()'>aggiungi persona</a> o <a nohref onclick='$("#impexp").toggle()'>importa / esporta dati</a><br />
<span id='addl' style='display: none;'><b>STEP 2:</b> <a nohref onclick='addLine()'>aggiungi prodotto</a><br />
<br />
<input type='button' value='ricomincia da zero!' onclick='if(confirm("Verranno persi i dati attuali. Continuare?"))emptyEverything();' />
<!--<input type='button' value='ricalcola' onclick='ricalcola()' />-->
</span>
<br />
<br />
<br />
<div id='people'></div><br />
<div id='center'></div>
<hr style='width: 500px; position: absolute; left: 10px;' />
<br />
<div id='results'></div>

 </body>
</html>
