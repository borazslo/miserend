<?php

function idoszak($i) {
    switch ($i) {
        case 'a': $tmp = 'Ádventi idő';
            break;
        case 'k': $tmp = 'Karácsonyi idő';
            break;
        case 'n': $tmp = 'Nagyböjti idő';
            break;
        case 'h': $tmp = 'Húsvéti idő';
            break;
        case 'e': $tmp = 'Évközi idő';
            break;
        case 's': $tmp = 'Szent ünnepe';
            break;
    }
    return $tmp;
}

function miserend_index() {
    global $user, $linkveg, $db_name, $m_id, $design_url, $_GET, $onload, $script;


    $attributes = unserialize(ATTRIBUTES);
    $languages = unserialize(LANGUAGES);


    $ma = date('Y-m-d');
    $holnap = date('Y-m-d', (time() + 86400));
    $mikor = '8:00-19:00';

    $query = "select id,ehm,nev from espereskerulet";
    $lekerdez = mysql_query($query);
    while (list($id, $ehm, $nev) = mysql_fetch_row($lekerdez)) {
        $espkerT[$ehm][$id] = $nev;
    }

    //MISEREND űRLAP	
    $searchform = array(
        'm_id' => array(
            'type' => 'hidden',
            'name' => "m_id",
            'value' => $m_id),
        'm_op' => array(
            'type' => 'hidden',
            'name' => "m_op",
            'value' => "keres"),
        'kulcsszo' => array(
            'name' => "kulcsszo",
            'size' => 20,
            'class' => 'keresourlap',
            'placeholder' => 'kulcsszó'),
        'varos' => array(
            'name' => "varos",
            'size' => 20,
            'id' => 'varos',
            'class' => 'keresourlap',
            'placeholder' => 'település'),
        'hely' => array(
            'name' => "hely",
            'size' => 20,
            'id' => 'varos',
            'class' => 'keresourlap'),
        'tavolsag' => array(
            'name' => "tavolsag",
            'size' => 1,
            'id' => 'tavolsag',
            'class' => 'keresourlap',
            'value' => 4)
    );


    $searchform['ehm'] = array(
        'name' => "ehm",
        'class' => 'keresourlap',
        'onChange' => "
						var a = document.getElementsByName('espker');	
						for (index = 0; index < a.length; ++index) {
						    console.log(a[index]);
						    a[index].style.display = 'none';
						}

						if(this.value!=0) {	
							document.getElementById('espkerlabel').style.display='inline';
							document.getElementById('ehm'+this.value).style.display='inline';

						} else {
							document.getElementById('espkerlabel').style.display='none';
						}");
    $searchform['ehm']['options'][0] = 'mindegy';
    $query = "select id,nev from egyhazmegye where ok='i' order by sorrend";
    $lekerdez = mysql_query($query);
    while (list($id, $nev) = mysql_fetch_row($lekerdez)) {
        $searchform['ehm']['options'][$id] = $nev;
    }

    foreach ($espkerT as $ehm => $espker) {
        $searchform['espker'][$ehm] = array(
            'name' => "espker",
            'id' => "ehm" . $ehm,
            'style' => "display:none",
            'class' => 'keresourlap');
        $searchform['espker'][$ehm]['options'][0] = 'mindegy';
        if (is_array($espker)) {
            foreach ($espker as $espid => $espnev) {
                $searchform['espker'][$ehm]['options'][$espid] = $espnev;
            }
        }
    }

    $searchform['gorog'] = array(
        'type' => 'checkbox',
        'name' => "gorog",
        'id' => "gorog",
        'class' => "keresourlap",
        'value' => "gorog"
    );

    $searchform['tnyelv'] = array(
        'name' => "tnyelv",
        'id' => "tnyelv",
        'class' => 'keresourlap',
        'options' => array(0 => 'bármilyen')
    );
    foreach ($languages as $abbrev => $language) {
        $searchform['tnyelv']['options'][$abbrev] = $language['name'];
    }

    //Mikor
    $mainap = date('w');
    if ($mainap == 0)
        $vasarnap = $ma;
    else {
        $kulonbseg = 7 - $mainap;
        $vasarnap = date('Y-m-d', (time() + (86400 * $kulonbseg)));
    }
    $searchform['mikor'] = array(
        'name' => "mikor",
        'id' => "mikor",
        'class' => 'keresourlap',
        'onChange' => "
						if(this.value == 'x') {document.getElementById('md').style.display='inline';} 
						else {document.getElementById('md').style.display='none';}",
        'options' => array($vasarnap => 'vasárnap', $ma => 'ma', $holnap => 'holnap', 'x' => 'adott napon:')
    );
    $searchform['mikordatum'] = array(
        'name' => "mikordatum",
        'id' => "md",
        'style' => "display:none",
        'class' => "keresourlap",
        'size' => "10",
        'value' => $ma
    );
    $searchform['mikor2'] = array(
        'name' => "mikor2",
        'id' => "mikor2",
        'style' => "margin-top:12px",
        'class' => 'keresourlap',
        'onChange' => "
						if(this.value == 'x') {
							document.getElementById('md2').style.display='inline'; 
							alert('FIGYELEM! Fontos a formátum!');} 
						else {document.getElementById('md2').style.display='none';}",
        'options' => array(0 => 'egész nap', 'de' => 'délelőtt', 'du' => 'délután', 'x' => 'adott időben:')
    );
    $searchform['mikorido'] = array(
        'name' => "mikorido",
        'id' => "md2",
        'style' => "display:none;",
        'class' => "keresourlap",
        'size' => "7",
        'value' => $mikor
    );

    //languages
    $searchform['nyelv'] = array(
        'name' => "nyelv",
        'id' => "nyelv",
        'class' => 'keresourlap',
        'options' => array(0 => 'mindegy')
    );
    foreach ($languages as $abbrev => $language) {
        $searchform['nyelv']['options'][$abbrev] = $language['name'];
    }

    //group music
    $music['na'] = '<i>meghatározatlan</i>';
    foreach ($attributes as $abbrev => $attribute) {
        if ($attribute['group'] == 'music')
            $music = array($abbrev => $attribute['name']) + $music;
    }
    foreach ($music as $value => $label) {
        $searchform['zene'][] = array(
            'type' => 'checkbox',
            'name' => "zene[]",
            'class' => "keresourlap",
            'value' => $value,
            'labelback' => $label,
            'checked' => true,
        );
    }

    //group age
    $age['na'] = '<i>meghatározatlan</i>';
    foreach ($attributes as $abbrev => $attribute) {
        if ($attribute['group'] == 'age')
            $age = array($abbrev => $attribute['name']) + $age;
    }
    foreach ($age as $value => $label) {
        $searchform['kor'][] = array(
            'type' => 'checkbox',
            'name' => "kor[]",
            'class' => "keresourlap",
            'value' => $value,
            'checked' => true,
            'labelback' => $label,
        );
    }

    //group rite
    $searchform['ritus'] = array(
        'name' => "ritus",
        'id' => "ritus",
        'class' => 'keresourlap',
        'options' => array(0 => 'mindegy')
    );
    foreach ($attributes as $abbrev => $attribute) {
        if ($attribute['group'] == 'liturgy' AND $attribute['isitmass'] == true)
            $searchform['ritus']['options'][$abbrev] = $attribute['name'];
    }

    $searchform['ige'] = array(
        'type' => 'checkbox',
        'name' => "liturgy[]",
        'id' => "liturgy",
        'checked' => true,
        'class' => "keresourlap",
        'value' => "ige"
    );

    $templomurlap = '';
    $miseurlap = $urlap;
    //AndroidReklám
    $androidreklam = androidreklam();

    //Napi gondolatok
    //Napi igehely

    $url = LirugicalDay();
    if ($url != false) {
        $readingsId = array();
        foreach ($url->Celebration as $celebration) {
            $unnep .= $celebration->StringTitle->span[0] . " (" . $celebration->LiturgicalCelebrationType . ") <br/>\n";
            $readingsId[] = " id = '" . $celebration->LiturgicalReadingsId . "' ";
        }

        $ev = $celebration->LiturgicalYearLetter;
        if (preg_match("/évközi/i", $celebration->LiturgicalSeason))
            $idoszak = 'e';
        elseif (preg_match("/nagyböjti/i", $celebration->LiturgicalSeason))
            $idoszak = 'n';
        elseif (preg_match("/húsvéti/i", $celebration->LiturgicalSeason))
            $idoszak = 'h';
        else
            $idoszak = "%";
        $nap = $celebration->LiturgicalWeek . ". hét, " . $url->DayOfWeek;

        $where = " WHERE ( ev = '{$ev}' AND idoszak = '{$idoszak}' AND nap = '{$nap}' ) OR (" . implode(' OR ', $readingsId) . " ) LIMIT 1";

        /* */

        /*
          //A liturgikus naptárból kiszedjük, hogy mi kapcsolódik a dátumhoz
          $query="select ige,szent,szin from lnaptar where datum='$datum'";
          list($ige,$szent,$szin)=mysql_fetch_row(mysql_query($query));
         */
        //Az igenaptárból kikeressük a mai napot
        //$query="select ev,idoszak,nap,oszov_hely,ujszov_hely,evang_hely,unnep,intro,gondolat from igenaptar where id='$ige'";
        $query = "select ev,idoszak,nap,oszov_hely,ujszov_hely,evang_hely,unnep,intro,gondolat from igenaptar " . $where;
        //echo $query;
        list($ev, $idoszak, $nap, $oszov_hely, $ujszov_hely, $evang_hely, $unnep, $intro, $gondolat) = mysql_fetch_row(mysql_query($query));
        $napiuzenet = nl2br($intro);
        $elmelkedes = $gondolat;

        if ((!empty($ev)) and ( $ev != '0'))
            $igenap.="$ev év, ";
        if (!empty($idoszak))
            $igenap.=idoszak($idoszak);
        if (!empty($nap))
            $igenap.=" $nap";

        if (empty($unnep))
            $unnep = $igenap;


        if ($szent > 0) {
            //Ha szent tartozik a napohoz
            $query = "select nev,intro,leiras from szentek where id='$szent'";
            list($szentnev, $szentintro, $szentleiras) = mysql_fetch_row(mysql_query($query));
            $unnep = $szentnev;
            $napiuzenet = nl2br($szentintro);
            $elmelkedes = $szentleiras;
        }

        //További szentek
        $s_ho = substr($datum, 5, 2);
        $s_nap = substr($datum, 8, 2);
        if ($s_ho[0] == '0')
            $s_ho = $s_ho[1];
        if ($s_nap[0] == '0')
            $s_nap = $s_nap[1];
        $query = "select id,nev,intro,leiras from szentek where ho='$s_ho' and nap='$s_nap' and id!='$szent'";
        $lekerdez = mysql_query($query);
        while (list($szid, $sznev, $szintro, $szleiras) = mysql_fetch_row($lekerdez)) {
            $szentidT[] = $szid;
            $szentnevT[] = $sznev;
            $introT[] = nl2br($szintro);
            $leirasT[] = $szleiras;
        }

        if (is_array($szentidT)) {
            foreach ($szentidT as $kulcs => $ertek) {
                if ($a > 0)
                    $megszentek.='<span class=link>, </span>';
                if (!empty($introT[$kulcs]) or ! empty($leirasT[$kulcs])) {
                    $link = "<a href=?m_id=1&m_op=szview&id=$ertek&szin=$_GET[szin]$linkveg class=link>";
                } else
                    $megszentek.='<span class=link>';
                $megszentek.=$link . $szentnevT[$kulcs];
                if (!empty($link))
                    $megszentek.='</a>';
                else
                    $megszentek.='</span>';
                $link = '';
                $a++;
            }
        }
    }

    if (!empty($unnep)) {
        $unnepkiir = "<span class=kiscim>$unnep</span>";
    }
    if (!empty($megszentek)) {
        $unnepkiir.='<br><span class=alap>(</span>' . $megszentek . '<span class=alap>)</span>';
    }

    $uzenet = "$unnepkiir<br>";
    $uzenet.="<br><div class=alapkizart>$napiuzenet</div>";
    $elmelkedes = "<span class=alapkizart>$elmelkedes</span>";


    $van_o = false;
    $van_u = false;
    $van_e = false;
    foreach (array('oszov', 'ujszov', 'evang') as $hely) {
        if (!empty(${$hely . "_hely"})) {
            $van_o = true;
            $tomb1 = explode(',', ${$hely . "_hely"});
            $tomb2 = explode('-', $tomb1[1]);
            $tomb3 = explode(' ', $tomb1[0]);
            $konyv = $tomb3[0];
            $fej = $tomb3[1];
            $vers = $tomb2[0];
            $link = "http://szentiras.hu/SZIT/" . preg_replace('/ /i', '', $konyv) . "/$fej#$vers";
            ${$hely . "_biblia"} = "<a href=$link target=_blank title='ez a rész és a környezete a Bibliában' class=link><img src=img/biblia.gif border=0 align=absmiddle> " . ${$hely . "_hely"} . "</a><br>";
        }
    }

    ///////////////////////////////////////////////////////////////////
    $igehelyek = $oszov_biblia . $ujszov_biblia . $evang_biblia;

    //Lit. naptár
    $naptar = "<span class=alap>naptár</span>";

    //Programajánló
    $programajanlo = "<span class=alap>kapcsolódó programok a naptárból<br>Fejlesztés alatt...</span>";

    //Képek
    $query = "SELECT t.id, t.nev, t.ismertnev, t.varos, k.fajlnev
    FROM kepek  k
    JOIN templomok t ON t.id=k.tid 
	 WHERE k.kiemelt = 'i' AND k.width < k.height AND k.height > 599 
    GROUP BY t.id 
    ORDER by RAND()
    LIMIT 15";

    if (!$lekerdez = mysql_query($query))
        echo "HIBA!<br>" . mysql_error();
    $mennyi = mysql_num_rows($lekerdez);
    if ($mennyi > 0) {
        $kepek.="\n<img src=$design_url/img/negyzet_kek.gif align=absmiddle><img src=img/space.gif width=5 height=5><span class=dobozcim_fekete>Képek templomainkról</span><br>";
        $konyvtaralap = "kepek/templomok";

        $kepek .= '<div style="height:180px"><div class="als-container" id="my-als-list">
            <span class="als-prev"><img src="img/als/thin_left_arrow_333.png" alt="prev" title="previous" /></span>
                <div class="als-viewport">
                    <ul class="als-wrapper">';

        $randoms = array();
        while ($random = mysql_fetch_assoc($lekerdez)) {
            $randoms[] = $random;
        }
        foreach ($randoms as $k => $random) {
            $random['konyvtar'] = $randoms[$k]['konyvtar'] = "$konyvtaralap/" . $random['id'];
            if (file_exists($random['$konyvtar'] . "/kicsi/" . $random['fajlnev'])) {
                //if(is_file("$konyvtar/kicsi/$fajlnev")) {
                @$info = getimagesize($random['$konyvtar'] . "/kicsi/" . $random['fajlnev']);
                $w1 = $info[0];
                $h1 = $info[1];
                if ($h1 > $w1 and $h1 > 90) {
                    $arany = 90 / $h1;
                    $ujh = 90;
                    $ujw = $w1 * $arany;
                } else {
                    $ujh = $h1;
                    $ujw = $w1;
                }

                $kepek .= "<li class='als-item colorbox'><a href=\"" . $random['konyvtar'] . "/" . $random['fajlnev'] . "\" title=\"" . $random['title'] . "\" class='als-color' onclick=\"ga('send','event','Inbound Links','Photos','?templom=" . $random['id'] . "')\">
	            <img src=\"" . $random['konyvtar'] . "/kicsi/" . $random['fajlnev'] . "\" title='" . $random['nev'] . " (" . $random['varos'] . ")' ></a>
	            
	                <div tid='" . $random['id'] . "' style='display:none;text-align:center'>
	                    <a href=\"?templom=" . $random['id'] . "\" title=\"" . $random['title'] . "\">
	                    <img src=\"" . $random['konyvtar'] . "/" . $random['fajlnev'] . "\" title='" . $random['nev'] . " (" . $random['varos'] . ")' align=\"center\" style=\"max-height:80%;display:block;margin-left:auto;margin-right:auto\">
	                    <div style=\"background-color:rgba(255,255,255,0.3);padding:10px;\" class=\"felsomenulink\">" . $random['nev'] . " (" . $random['varos'] . ")</div>
	                    </a>
	                </div>
	            
	            </li>\n";
            } else {
                
            }
        }
        if ($mennyi < 4)
            for ($i = 0; $i < 4 - $mennyi; $i++)
                $kepek .= "<li class='als-item'></li>";
        $kepek.='</ul>
            </div>
            <span class="als-next"><img src="img/als/thin_right_arrow_333.png" alt="next" title="next" /></span>
            </div></div>';

        $scrollable .= '<script>
			$(document).ready(function(){                
                $("#my-als-list").als(	{visible_items: ';
        if ($mennyi < 4)
            $scrollable .= 4;
        else
            $scrollable .= 4;
        $scrollable .= ',	circular: "no"});                      
                
                $("li.colorbox").each(function() {
                    $(this).colorbox({
                        html: $(this).find("div").html(),
                        rel: "group_random",
                        transition:"fade",
                        maxHeight:"98%"
                    },
                    function() {
                        ga(\'send\',\'event\',\'Photos\',\'fooldal\',$(this).find("div").attr("tid"));
                    }                        
                   );
                });
                /*$(".als-color").colorbox({rel:\'als-color\', transition:"fade",maxHeight:"98%"},
                    function() {
                        ga(\'send\',\'event\',\'Photos\',\'fooldal\',\'' . $tid . '\')        });            
                
            */ });
        </script>';
        $kepek .= $scrollable;
    }

    //statisztika
    $statisztika = miserend_printRegi();

    global $twig;
    $variables = array(
        'favorites' => $user->getFavorites(),
        'scripts' => $script,
        'formnyit' => $formnyit,
        'formvalaszt' => $formvalaszt,
        'miseurlap' => $miseurlap,
        'searchform' => $searchform,
        'androidreklam' => $androidreklam,
        'templomurlap' => $templomurlap,
        'kepek' => $kepek,
        'kep' => $randoms[0],
        'uzenet' => $uzenet,
        'igehelyek' => $igehelyek,
        'elmelkedes' => $elmelkedes,
        'design_url' => $design_url,
        'alert' => LiturgicalDayAlert('html'),
        'campaign' => updatesCampaign(),
    );
    $variables['template'] = 'home';

    return $variables;
}

function miserend_templomkeres() {
    global $user;
    global $db_name, $design_url, $linkveg, $m_id;

    $query = "select id,nev from egyhazmegye where ok='i' order by sorrend";
    $lekerdez = mysql_query($query);
    while (list($id, $nev) = mysql_fetch_row($lekerdez)) {
        $ehmT[$id] = $nev;
    }

    $query = "select id,ehm,nev from espereskerulet";
    $lekerdez = mysql_query($query);
    while (list($id, $ehm, $nev) = mysql_fetch_row($lekerdez)) {
        $espkerT[$ehm][$id] = $nev;
    }


    $varos = $_POST['varos'];
    $kulcsszo = $_POST['kulcsszo'];
    $ehm = $_POST['ehm'];
    //	$megye=$_POST['megye'];
    if (empty($_POST['espker'])) {
        $espkerpT = $_POST['espkerT'];
        $espker = $espkerpT[$ehm];
    } else
        $espker = $_POST['espker'];


    //Templom űrlap
    $templomurlap = "\n<div style='display: none'><form method=post><input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=keres></div>";
    $templomurlap .="<input type=hidden id=keresestipus name=keresestipus value=0>";
    //$templomurlap .= "<input type=hidden name=tavolsag id=tavolsag size=20 class=keresourlap value='".$_REQUEST['tavolsag']."'>";
    //$templomurlap .= "<input type=hidden name=hely id=tavolsag size=20 class=keresourlap value='".$_REQUEST['hely']."'>";
    $templomurlap.="\n<img src=img/space.gif width=5 height=10><br><span class=kiscim>Település: </span><input type=text name=varos id=varos size=20 class=keresourlap value='$varos'><br><img src=img/space.gif width=5 height=8>";

    $templomurlap.="<br><span class=kiscim>Kulcsszó: </span><input type=text name=kulcsszo size=20 class=keresourlap value='$kulcsszo'><br><img src=img/space.gif width=5 height=8>";

    //Egyházmegye
    $templomurlap.="<br><span class=kiscim>Egyházmegye: </span><br><img src=img/space.gif width=5 height=5><br><img src=img/space.gif width=10 height=5><select name=ehm class=keresourlap onChange=\"if(this.value!=0) {";
    foreach ($ehmT as $id => $nev) {
        $templomurlap.="document.getElementById($id).style.display='none'; ";
    }
    $templomurlap.="document.getElementById(this.value).style.display='inline'; document.getElementById('valassz').style.display='none'; } else {";
    foreach ($ehmT as $id => $nev) {
        $templomurlap.="document.getElementById($id).style.display='none'; ";
    }
    $templomurlap.="document.getElementById('valassz').style.display='inline';}\"><option value=0>mindegy</option>";
    foreach ($ehmT as $id => $nev) {
        $templomurlap.="<option value=$id";
        if ($id == $ehm)
            $templomurlap.=' selected';
        $templomurlap.=">$nev</option>";

        if ($id == $ehm)
            $espkerurlap.="<select id=$id name=espkerT[$id] class=keresourlap style='display: inline'><option value=0>mindegy</option>";
        else
            $espkerurlap.="<select id=$id name=espkerT[$id] class=keresourlap style='display: none'><option value=0>mindegy</option>";
        if (is_array($espkerT[$id])) {
            foreach ($espkerT[$id] as $espid => $espnev) {
                $espkerurlap.="<option value=$espid";
                if ($espker == $espid)
                    $espkerurlap.=' selected';
                $espkerurlap.=">$espnev</option>";
            }
        }
        $espkerurlap.="</select>";
    }
    $templomurlap.="</select><br><img src=img/space.gif width=5 height=8>";

    //Espereskerület
    $templomurlap.="<br><span class=kiscim>Espereskerület: </span><br><img src=img/space.gif width=5 height=5><br><img src=img/space.gif width=10 height=5>";
    if (empty($ehm))
        $templomurlap.="<div id='valassz' style='display: inline' class=keresourlap>Először válassz egyházmegyét.</div>";
    $templomurlap.=$espkerurlap;
    $templomurlap.="<br><img src=img/space.gif width=5 height=8>";

    $templomurlap.="\n<br><img src=img/space.gif width=5 height=10><div align=right><input type=submit value=keresés class=keresourlap><br><img src=img/space.gif width=5 height=10></div><div style='display: none'></form></div>";


    $postdata.="<input type=hidden name=varos value='$varos'>";
    $postdata.="<input type=hidden name=tavolsag value='" . $_REQUEST['tavolsag'] . "'>";
    $postdata.="<input type=hidden name=hely value='" . $_REQUEST['hely'] . "'>";
    $postdata.="<input type=hidden name=kulcsszo value='$kulcsszo'>";
    $postdata.="<input type=hidden name=gorog value='" . $_REQUEST['gorog'] . "'>";
    $postdata.="<input type=hidden name=tnyelv value='" . $_REQUEST['tnyelv'] . "'>";

    $postdata.="<input type=hidden name=espker value='$espker'>";
    $postdata.="<input type=hidden name=ehm value='$ehm'>";

    $min = $_POST['min'];
    $leptet = $_POST['leptet'];
    if ($min < 0 or empty($min))
        $min = 0;
    if (empty($leptet))
        $leptet = 20;

    $results = searchChurches($_POST, $min, $leptet);

    $mennyi = $results['sum'];

    if ($mennyi == 1) {
        //ga('send','event','Outgoing Links','click','".$pleb_url."');
        //header ("Location: ?templom=".$talalat['id']);
        echo "
        <script type='text/javascript'>
            (function(i,s,o,g,r,a,m){i[\"GoogleAnalyticsObject\"]=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,\"script\",\"//www.google-analytics.com/analytics.js\",\"ga\");

    ga(\"create\", \"UA-3987621-4\", \"miserend.hu\");
    ga('send','event','Search','fast','" . $varos . $kulcsszo . $ehm . "');
    
    window.location = '?templom=" . $results['results'][0]['id'] . "';
           
         </script>";

        die();
    }

    $kezd = $min + 1;
    $veg = $mennyi;
    if ($min > 0) {
        $leptetprev.="\n<form method=post><input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=keres>";
        $leptetprev.=$postdata;
        $leptetprev.="<input type=hidden name=min value=$prev>";
        $leptetprev.="\n<input type=submit value=Előző class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
    }
    if ($mennyi > $leptet) {
        $veg = $min + $leptet;
        $prev = $min - $leptet;
        if ($prev < 0)
            $prev = 0;
        $next = $min + $leptet;

        if ($mennyi > $min + $leptet) {
            $leptetnext.="\n<form method=post><input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=keres><input type=hidden name=min value=$next>";
            $leptetnext.=$postdata;
            $leptetnext.="\n<input type=submit value=Következő class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
        }
    }

    if ($mennyi > 0) {
		$tartalom.="<span class=alap>Összesen: $mennyi találat<br>Listázás: $kezd - $veg</span><br><br>";
        foreach ($results['results'] as $templom) {
            $tid = $templom['id'];
            $tnev = $templom['nev'];
            $tismertnev = $templom['ismertnev'];
            $tvaros = $templom['varos'];
            $letrehozta = $templom['letrehozta'];
            $tartalom.="<a href=?templom=$tid$linkveg class=felsomenulink title='$tismertnev'><b>$tnev</b> <font color=#8D317C>($tvaros)</font></a>";
            if ($user->checkRole('miserend') OR $letrehozta == $user->login)
                $tartalom.=" <a href=?m_id=27&m_op=addtemplom&tid=$tid$linkveg><img src=img/edit.gif title='szerkesztés' align=absmiddle border=0></a> <a href=?m_id=27&m_op=addmise&tid=$tid$linkveg><img src=img/mise_edit.gif align=absmiddle border=0 title='mise módosítása'></a>";
            if ($tismertnev != '')
                $tartalom .= "<br/><span class=\"alap\" style=\"margin-left: 20px; font-style: italic;\">" . $tismertnev . "</span>";
            $tartalom.="<br><img src=img/space.gif width=4 height=5><br>";
        }
        $tartalom.='<br>' . $leptetprev . $leptetnext;
    }
    else {
        $tartalom.='<span class=alap>A keresés nem hozott eredményt.</span>';
    }

    $focim = "Keresés a templomok között";
    global $twig;
    $variables = array(
        'focim' => $focim,
        'content' => $tartalom,
        'templomurlap' => $templomurlap,
        'design_url' => $design_url);

    $variables['template'] = 'search/resultsChurches';
    return $variables;
}

function miserend_misekeres() {
    global $db_name, $design_url, $linkveg, $m_id, $_POST, $_GET;
    global $user;

    $mikor = $_POST['mikor'];
    $mikordatum = $_POST['mikordatum'];
    if ($mikor != 'x')
        $mikordatum = $mikor;


    $mikor2 = $_POST['mikor2'];
    $mikorido = $_POST['mikorido'];
    $varos = $_POST['varos'];
    $ehm = $_POST['ehm'];
    $espkerT = $_POST['espkerT'];
    $nyelv = $_POST['nyelv'];
    $zene = $_POST['zene'];
    $kor = $_POST['kor'];
    $ritus = $_POST['ritus'];
    $ige = $_POST['ige'];


    $min = $_POST['min'];
    if (!isset($min))
        $min = 0;
    $leptet = $_POST['leptet'];
    if (!isset($leptet))
        $leptet = 25;

    $ma = date('Y-m-d');
    $holnap = date('Y-m-d', (time() + 86400));

    if ($ehm > 0) {
        $query = "select nev from egyhazmegye where id=$ehm and ok='i'";
        $lekerdez = mysql_query($query);
        list($ehmnev) = mysql_fetch_row($lekerdez);
    }

    if ($espkerT[$ehm] > 0) {
        $query = "select nev from espereskerulet where id='$espkerT[$ehm]'";
        $lekerdez = mysql_query($query);
        list($espkernev) = mysql_fetch_row($lekerdez);
    }

    $zeneT = array('g' => 'gitáros', 'o' => 'orgonás', 'cs' => 'csendes', 'na' => 'meghátorazatlan');
    $korT = array('csal' => 'családos', 'd' => 'diák', 'ifi' => 'ifjúsági', 'na' => 'meghátorazatlan');
    $ritusT = array('gor' => 'görögkatolikus', 'rom' => 'római katolikus', 'regi' => 'régi rítusú');
    $nyelvekT = array('h' => 'magyar', 'en' => 'angol', 'de' => 'német', 'it' => 'olasz', 'va' => 'latin', 'gr' => 'görög', 'sk' => 'szlovák', 'hr' => 'horvát', 'pl' => 'lengyel', 'si' => 'szlovén', 'ro' => 'román', 'fr' => 'francia', 'es' => 'spanyol');

    $tartalom.="\n<span class=kiscim>Keresési paraméterek:</span><br><span class=alap>";
    if (isset($_REQUEST['kulcsszo']) AND $_REQUEST['kulcsszo'] != '') {
        $tartalom.="<img src=$design_url/img/negyzet_lila.gif align=absmidle> Kulcsszó: " . $_REQUEST['kulcsszo'] . "<br/>";
        $leptet_urlap.="<input type=hidden name=kulcsszo value='" . $_REQUEST['kulcsszo'] . "'>";
    }
    if (isset($_REQUEST['hely']) AND $_REQUEST['hely'] != '') {
        $_REQUEST['hely_geocode'] = mapquestGeocode($_REQUEST['hely']);
        $tartalom.="<img src=$design_url/img/negyzet_lila.gif align=absmidle> " . $_REQUEST['hely'] . " + " . $_REQUEST['tavolsag'] . " km<br/><img src='" . $_REQUEST['hely_geocode']['mapUrl'] . "'><br/>";
        $leptet_urlap.="<input type=hidden name=hely value='" . $_REQUEST['hely'] . "'>";
        $leptet_urlap.="<input type=hidden name=tavolsag value='" . $_REQUEST['tavolsag'] . "'>";
    }
    if (!empty($varos)) {
        $varos = ucfirst($varos);
        $tartalom.="<img src=$design_url/img/negyzet_lila.gif align=absmidle> $varos településen<br/>";
        $leptet_urlap.="<input type=hidden name=varos value='$varos'>";
    }
    if (isset($_REQUEST['gorog']) AND $_REQUEST['gorog'] == 'gorog') {
        $tartalom.="<br><img src=$design_url/img/negyzet_lila.gif align=absmidle> Csak görögkatolikus templomokban.";
        $leptet_urlap.="<input type=hidden name=gorog value='gorog'>";
    }
    if (isset($_REQUEST['tnyelv']) AND $_REQUEST['tnyelv'] != '0') {
        $tartalom.="<br><img src=$design_url/img/negyzet_lila.gif align=absmidle>Amelyik templomban van '" . $_REQUEST['tnyelv'] . "' nyelvű mise.<br/>";
    }
    $leptet_urlap.="<input type=hidden name=tnyelv value='" . $_REQUEST['tnyelv'] . "'>";

    if (!empty($ehmnev)) {
        $tartalom.="<br><img src=$design_url/img/negyzet_lila.gif align=absmidle> $ehmnev egyházmegyében,";
        $leptet_urlap.="<input type=hidden name=ehm value='$ehm'>";
    }
    if (!empty($espkernev)) {
        $tartalom.="<br><img src=$design_url/img/negyzet_lila.gif align=absmidle> $espkernev espereskerületben,";
        $leptet_urlap.="<input type=hidden name=espkerT[$ehm] value='$espkerT[$ehm]'>";
    }
    if (!empty($ehmnev))
        $tartalom .= '<br/>';
    $tartalom.="<img src=$design_url/img/negyzet_lila.gif align=absmidle> ";
    if ($mikordatum == $ma) {
        $tartalom.='ma';
        $leptet_urlap.="<input type=hidden name=mikor value='$ma'>";
    } elseif ($mikordatum == $holnap) {
        $tartalom.='holnap';
        $leptet_urlap.="<input type=hidden name=mikor value='$holnap'>";
    } else {
        $mev = substr($mikordatum, 0, 4);
        $mho = substr($mikordatum, 5, 2);
        $mnap = substr($mikordatum, 8, 2);
        $mnapnev = date('w', mktime(0, 0, 0, $mho, $mnap, $mev));
        $napokT = array('vasárnap', 'hétfő', 'kedd', 'szerda', 'csütörtök', 'péntek', 'szombat');
        $mikornap = ' ' . $napokT[$mnapnev];
        $tartalom.=$mikordatum . $mikornap;

        $leptet_urlap.="<input type=hidden name=mikor value='x'>";
        $leptet_urlap.="<input type=hidden name=mikordatum value='$mikordatum'>";
    }
    $tartalom.=' ';
    if ($mikor2 == 'de') {
        $tartalom.='délelőtt,';
        $leptet_urlap.="<input type=hidden name=mikor2 value='de'>";
    } elseif ($mikor2 == 'du') {
        $tartalom.='délután,';
        $leptet_urlap.="<input type=hidden name=mikor2 value='du'>";
    } elseif ($mikor2 == 'x') {
        $tartalom.=$mikorido;
        $leptet_urlap.="<input type=hidden name=mikor2 value='x'>";
        $leptet_urlap.="<input type=hidden name=mikorido value='$mikorido'>";
    } else {
        $tartalom.='egész nap,';
        $leptet_urlap.="<input type=hidden name=mikor2 value='0'>";
    }
    if (!empty($nyelv) or ( !empty($zene) AND count($zene) < 3) or ( !empty($kor) AND count($kor) < 4))
        $tartalom.="<br><img src=$design_url/img/negyzet_lila.gif align=absmidle> ";
    if (!empty($nyelv)) {
        $tartalom.="$nyelvekT[$nyelv] nyelvű, ";
        //		$leptet_urlap.="<input type=hidden name=nyelv value='$nyelv'>";
    }
    $leptet_urlap.="<input type=hidden name=nyelv value='$nyelv'>";

    if (!empty($zene)) {
        foreach ($zene as $z) {
            if (count($zene) < 3)
                $tartalom.="$zeneT[$z], ";
            $leptet_urlap.="<input type=hidden name=zene[] value='$z'>";
        }
    }
    if (!empty($kor)) {
        foreach ($kor as $k) {
            if (count($kor) < 4)
                $tartalom.="$korT[$k], ";
            $leptet_urlap.="<input type=hidden name=kor[] value='$k'>";
        }
    }
    if (!empty($ritus)) {
        $tartalom.="<br><img src=$design_url/img/negyzet_lila.gif align=absmidle> $ritusT[$ritus]";
        $leptet_urlap.="<input type=hidden name=ritus value='$ritus'>";
    }
    if (!empty($ige)) {
        $tartalom.="<br><img src=$design_url/img/negyzet_lila.gif align=absmidle> igeliturgiák is";
        $leptet_urlap.="<input type=hidden name=ige value='yes'>";
    }

    $tartalom.="</span><br/>" . LiturgicalDayAlert('html', $mikordatum);

    if (!empty($_POST['leptet']))
        $visszalink = "?$linkveg";
    else
        $visszalink = "javascript:history.go(-1);";
    $templomurlap = "<img src=img/space.gif width=5 height=6><br><a href=$visszalink class=link><img src=img/search.gif width=16 height=16 border=0 align=absmiddle hspace=2><b>Vissza a főoldali keresőhöz</b></a><br><img src=img/space.gif width=5 height=6>";

    /*

      if(isset($results['results'])) foreach($results['results'] as $result) {
      $tid = $result['tid'];
      //$tnev = $result['']; ,$tismertnev,$tvaros,$letrehozta,$mido,$mnyelv,$mmegjegyzes

      $nyelvikon='';
      if(empty($templom[$tid])) {
      $templomT[$tid]="<img src=img/templom1.gif align=absmiddle width=16 height=16 hspace=2><a href=?templom=$tid$linkveg class=felsomenulink><b>$tnev</b> <font color=#8D317C>($tvaros)</font></a><br><span class=alap style=\"margin-left: 20px; font-style: italic;\">$tismertnev</span>";
      if($user->checkRole('miserend')) $templomT[$tid].=" <a href=?m_id=27&m_op=addtemplom&tid=$tid$linkveg><img src=img/edit.gif title='szerkesztés' align=absmiddle border=0></a>  <a href=?m_id=27&m_op=addmise&tid=$tid$linkveg><img src=img/mise_edit.gif align=absmiddle border=0 title='mise módosítása'></a>";
      elseif($letrehozta==$user->login) $templomT[$tid].=" <a href=?m_id=29&m_op=addtemplom&tid=$tid$linkveg><img src=img/edit.gif title='szerkesztés' align=absmiddle border=0></a> <a href=?m_id=29&m_op=addmise&tid=$tid$linkveg><img src=img/mise_edit.gif align=absmiddle border=0 title='mise módosítása'></a>";
      }
      if(!empty($mmegjegyzes)) $megj="<img src=$design_url/img/info2.gif border=0 title='$mmegjegyzes' align=absmiddle width=16 height=16>";
      else $megj='';

      if(strstr($mnyelv,'de')) $nyelvikon.="<img src=img/zaszloikon/de.gif width=16 height=11 vspace=2 align=absmiddle title='német nyelvű mise'>";
      if(strstr($mnyelv,'it')) $nyelvikon.="<img src=img/zaszloikon/it.gif width=16 height=11 vspace=2 align=absmiddle title='olasz nyelvű mise'>";
      if(strstr($mnyelv,'en')) $nyelvikon.="<img src=img/zaszloikon/en.gif width=16 height=11 vspace=2 align=absmiddle title='angol nyelvű mise'>";
      if(strstr($mnyelv,'hr')) $nyelvikon.="<img src=img/zaszloikon/hr.gif width=16 height=11 vspace=2 align=absmiddle title='horvát nyelvű mise'>";
      if(strstr($mnyelv,'gr')) $nyelvikon.="<img src=img/zaszloikon/gr.gif width=16 height=11 vspace=2 align=absmiddle title='görög nyelvű mise'>";
      if(strstr($mnyelv,'va')) $nyelvikon.="<img src=img/zaszloikon/va.gif width=16 height=11 vspace=2 align=absmiddle title='latin nyelvű mise'>";
      if(strstr($mnyelv,'si')) $nyelvikon.="<img src=img/zaszloikon/si.gif width=16 height=11 vspace=2 align=absmiddle title='szlovén nyelvű mise'>";
      if(strstr($mnyelv,'ro')) $nyelvikon.="<img src=img/zaszloikon/ro.gif width=16 height=11 vspace=2 align=absmiddle title='román nyelvű mise'>";
      if(strstr($mnyelv,'sk')) $nyelvikon.="<img src=img/zaszloikon/sk.gif width=16 height=11 vspace=2 align=absmiddle title='szlovák nyelvű mise'>";
      if(strstr($mnyelv,'pl')) $nyelvikon.="<img src=img/zaszloikon/pl.gif width=16 height=11 vspace=2 align=absmiddle title='lengyel nyelvű mise'>";
      if(strstr($mnyelv,'fr')) $nyelvikon.="<img src=img/zaszloikon/fr.gif width=16 height=11 vspace=2 align=absmiddle title='francia nyelvű mise'>";

      if($mido<$mostido and $mikordatum==$ma) $elmult=true;
      else $elmult=false;
      if($mido=='00:00:00') $mido='?';
      if($mido[0]=='0') $mido=substr($mido,1,4);
      else $mido=substr($mido,0,5);
      if($elmult) $mido="<font color=#555555>$mido</font>";
      else $mido="<b>$mido</b>";
      $miseT[$tid][]="<img src=img/clock.gif width=16 height=16 align=absmiddle hspace=2><span class=alap>$mido</span>$nyelvikon$megj &nbsp; ";
      }
     */
    $results = searchMasses($_POST, $min, $leptet);
    $mennyi = $results['sum'];

    $kezd = $min + 1;
    $prev = $min - $leptet;
    if ($prev < 0)
        $prev = 0;
    $veg = $mennyi;
    if ($min > 0) {
        $leptetprev.="\n<form method=post><input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=keres><input type=\"hidden\" id=\"misekereses\" name=\"misekereses\" value=\"1\">";
        $leptetprev.=$leptet_urlap;
        $leptetprev.="<input type=hidden name=min value=$prev>";
        $leptetprev.="\n<input type=submit value=Előző class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
    }
    if ($mennyi > $leptet) {
        $veg = $min + $leptet;

        $next = $min + $leptet;

        if ($mennyi > $min + $leptet) {
            $leptetnext.="\n<form method=post><input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=keres><input type=hidden name=min value=$next><input type=\"hidden\" id=\"misekereses\" name=\"misekereses\" value=\"1\">";
            $leptetnext.=$leptet_urlap;
            $leptetnext.="\n<input type=submit value=Következő class=urlap><input type=text size=2 value=$leptet name=leptet class=urlap></form>";
        }
    }



    if ($mennyi == 0) {
        $tartalom.='<br/><span class=alap><strong>Sajnos nincs találat</strong></span>';
        //$tartalom.='<span class=alap>Elnézést kérünk, a kereső technikai hiba miatt nem üzemel. Javításán már dolgozunk.</span>';
    } else {
        //$tartalom.="<span class=kiscim>Összesen $mennyi templomban van megfelelő mise.</span><br><br>";
        $tartalom.="<br><span class=alap>Összesen: $mennyi templomban van megfelelő mise.<br>Listázás: $kezd - $veg</span><br><br>";

        //echo "<pre>".print_r($results['results'],1)."</pre>";
        foreach ($results['churches'] as $result) {
            $tartalom .= "<img src=img/templom1.gif align=absmiddle width=16 height=16 hspace=2>
				<a href=?templom=" . $result['tid'] . "$linkveg class=felsomenulink><b>" . $result['nev'] . "</b> <font color=#8D317C>(" . $result['varos'] . ")</font></a><br><span class=alap style=\"margin-left: 20px; font-style: italic;\">" . $result['ismertnev'] . "</span>";
            if ($user->checkRole('miserend') OR $result['letrehozta'] == $user->login)
                $tartalom.=" <a href=?m_id=27&m_op=addtemplom&tid=" . $result['tid'] . "$linkveg><img src=img/edit.gif title='szerkesztés' align=absmiddle border=0></a>  <a href=?m_id=27&m_op=addmise&tid=" . $result['tid'] . "$linkveg><img src=img/mise_edit.gif align=absmiddle border=0 title='mise módosítása'></a>";

            $tartalom.=$ertek . '<br> &nbsp; &nbsp; &nbsp;';

            if ($_REQUEST['mikor'] == 'x')
                $_REQUEST['mikor'] = $_REQUEST['mikordatum'];
            //$masses = getMasses($result['tid'],$_REQUEST['mikordatum']);
            //$masses = searchMasses(array_merge(array('templom'=>$result['tid']),$_POST));
            foreach ($result['masses'] as $mass) {
                $tartalom .="<img src=img/clock.gif width=16 height=16 align=absmiddle hspace=2><span class=alap>" . substr($mass['ido'], 0, 5) . "</span>";

                $mass['nyelv'] = decodeMassAttr($mass['nyelv']);
                foreach ($mass['nyelv'] as $milyen)
                    $tartalom.= '<img src="' . $design_url . 'img/' . $milyen['file'] . '" class="massinfo" width=14 title="' . $milyen['description'] . '"" height=14 align=absmiddle style="margin-top:0px;margin-left:1px">
    					<span class="massfullinfo" style="display:none" >' . $milyen['description'] . '</span>';

                $mass['milyen'] = decodeMassAttr($mass['milyen']);
                foreach ($mass['milyen'] as $milyen)
                    $tartalom.= '<img src="' . $design_url . 'img/' . $milyen['file'] . '" class="massinfo" width=14 title="' . $milyen['description'] . '"" height=14 align=absmiddle style="margin-top:0px;margin-left:1px">
    					<span class="massfullinfo" style="display:none">' . $milyen['description'] . '</span>';

                if ($mass['megjegyzes'] != '')
                    $tartalom.= '<img src="' . $design_url . 'img/info2.gif" class="massinfo" width=14 title="' . $milyen['megjegyzes'] . '"  height=14 align=absmiddle style="margin-top:0px;margin-left:1px">
					<span class="massfullinfo" style="display:none">' . $mass['megjegyzes'] . '</span>';
            }
            //$tartalom .= print_r($masses,1);

            $tartalom.="<br><img src=img/space.gif width=4 height=8><br>";
        }
    }

    //Léptetés
    if ($mennyi > $min + $leptet) {
        $next = $min + $leptet;
        $leptetes = "<br><form method=post><input type=hidden name=m_id value=$m_id><input type=hidden name=m_op value=keres><input type=\"hidden\" id=\"keresestipus\" name=\"keresestipus\" value=\"1\">";
        $leptetes.=$leptet_urlap;
        $leptetes.="<input type=submit value=Következő class=urlap><input type=text name=leptet value=$leptet class=urlap size=2><input type=hidden name=min value=$next></form>";
    }
    $tartalom.=$leptetprev . $leptetnext;


    $focim = "Szentmise kereső";
    global $twig;
    $variables = array(
        'focim' => $focim,
        'content' => $tartalom,
        'templomurlap' => $templomurlap,
        'design_url' => $design_url);
    $variables['template'] = 'search/resultsChurches';

    return $variables;
}

function miserend_view() {
    global $linkveg, $db_name, $elso, $m_id, $m_op, $design_url, $deisgn, $onload, $script, $titlekieg, $meta;
    global $twig, $user;

    $tid = $_REQUEST['tid'];

    $church = getChurch($tid);
    if ($church != array())
        $vane = 1;
    foreach ($church as $k => $i)
        $$k = $i;

    $ma = date('Y-m-d');
    if ($frissites > 0) {
        $frissitve = $frissites;
        $frissites = str_replace('-', '.', $frissites) . '.';
        //$frissites="<span class=kicsi_kek><b><u>Frissítve:</u></b><br>$frissites</span>";
    }


    $titlekieg = " - $nev ($varos)";
    if ($vane != 1)
        $titlekieg = "404";

    if (!empty($turistautak)) {
        //$terkep="<br><a href=http://turistautak.hu/poi.php?id=$turistautak target=_blank title='További infók'><img src=http://www.geocaching.hu/images/mapcache/poi_$turistautak.gif border=0 vspace=5 hspace=5></a>";
    }

    $ev = date('Y');
    $mostido = date('H:i:s');
    $mainap = date('w');
    if ($mainap == 0)
        $mainap = 7;
    $tolig = $nyariido . '!' . $teliido;
    $tolig = str_replace('-', '.', $tolig);
    $tolig = str_replace("$ev.", '', $tolig);
    $tolig = str_replace('!', ' - ', $tolig);
    if ($ma >= $nyariido and $ma <= $teliido) {
        $nyari = "<div align=center><span class=alap><b><font color=#B51A7E>nyári</font></b></span><br><span class=kicsi>($tolig)</span></div>";
        $teli = "<div align=center><span class=alap>téli</span></div>";
        $aktiv = 'ny';
    } else {
        $nyari = "<div align=center><span class=alap>nyári</span><br><span class=kicsi>($tolig)</span></div>";
        $teli = "<div align=center><span class=alap><b><font color=#B51A7E>téli</font></b></span></div>";
        $aktiv = 't';
    }


    //Miseidőpontok
    $misek = getMasses($tid);

    if ($user->checkRole('miserend') OR $user->checkRole('ehm:' . $egyhazmegye) OR ( isset($responsible) AND in_array($user->login, $responsible))) {
        $nev.=" <a href=?m_id=27&m_op=addtemplom&tid=$tid$linkveg><img src=img/edit.gif align=absmiddle border=0 title='Szerkesztés/módosítás'></a> <a href=?m_id=27&m_op=addmise&tid=$tid$linkveg><img src=img/mise_edit.gif align=absmiddle border=0 title='mise módosítása'></a>";

        $query = "select allapot from eszrevetelek where hol_id = '" . $tid . "' GROUP BY allapot ORDER BY allapot limit 5;";
        $result = mysql_query($query);
        $allapotok = array();
        while ($row = mysql_fetch_assoc($result)) {
            if ($row['allapot'])
                $allapotok[] = $row['allapot'];
        }
        if (in_array('u', $allapotok))
            $nev.=" <a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid',550,500);\"><img src=img/csomag.gif title='Új észrevételt írtak hozzá!' align=absmiddle border=0></a> ";
        elseif (in_array('f', $allapotok))
            $nev.=" <a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid',550,500);\"><img src=img/csomagf.gif title='Észrevétel javítása folyamatban!' align=absmiddle border=0></a> ";
        elseif (count($allapotok) > 0)
            $nev.=" <a href=\"javascript:OpenScrollWindow('naplo.php?kod=templomok&id=$tid',550,500);\"><img src=img/csomag1.gif title='Észrevételek!' align=absmiddle border=0></a> ";
    }
    if (!empty($ismertnev))
        $ismertnev = $ismertnev; //"<span class=alap><i><b>Közismert nevén:</b></i><br></span><span class=dobozfocim_fekete><b><font color=#AC007A>$ismertnev</font></b></span><br><img src=img/space.gif width=5 height=7><br>";
    $cim = "<span class=alap><i>Cím:</i> <u>$varos, $cim</u></span>";

    if ($checked > 0) {
        $staticmap = "kepek/staticmaps/" . $tid . "_227x140.jpeg";
        if (file_exists($staticmap))
            $cim .= "<a href=\"http://www.openstreetmap.org/?mlat=$lat&mlon=$lng#map=15/$lat/$lng\" target=\"_blank\"><img src='kepek/staticmaps/" . $tid . "_227x140.jpeg'></a>";
        else
            $cim .= "<br/><span class=alap><i>Térképen:</i> <u><a href=\"http://www.openstreetmap.org/?mlat=$lat&mlon=$lng\">$lat, $lng</a></u></span>";
    } else
        $cim .= "<br/><span class=alap><u><a href=\"http://terkep.miserend.hu/?templom=$tid\">Segíts megtalálni a térképen!</a></u></span>";

    $kapcsolat = nl2br($plebania);
    if (!empty($pleb_url))
        $kapcsolat.="<br/><div style=\"width: 230px;white-space: nowrap;overflow: hidden;o-text-overflow: ellipsis;text-overflow: ellipsis;\">Weboldal: <a href=$pleb_url target=_blank class=link title='$pleb_url'  onclick=\"ga('send','event','Outgoing Links','click','" . $pleb_url . "');\">" . preg_replace("/http:\/\//", "", $pleb_url) . "</a></div>";
    if (!empty($pleb_eml))
        $kapcsolat.="<div style=\"width: 230px;white-space: nowrap;overflow: hidden;o-text-overflow: ellipsis;text-overflow: ellipsis;\">Email: <a href='mailto:$pleb_eml' class=link>$pleb_eml</a></div>";

    $eszrevetel = "<p class=alapkizart>Ha észrevételed van a templommal vagy a miserenddel kapcsolatban, írd meg nekünk!</p>
    <div align=center><a href=\"javascript:OpenNewWindow('eszrevetel.php?id=$tid&kod=templomok',450,530);\" class=link><font color=#8D317C size='+1'><b>Észrevétel beküldése</b></font></a></div>
    <div align=center><a href=\"javascript:OpenNewWindow('kepkuldes.php?id=$tid&kod=templomok',450,530);\" class=link><font color=#8D317C size=''><b>Új kép beküldése</b></font></a></div>";

    ////////////////////////
    //$sz1='<span class=kicsi>a szolgáltatás átmenetileg szünetel</span>';
    //$sz2='<span class=kicsi>a szolgáltatás átmenetileg szünetel</span>';

    $marcsak = (int) ((strtotime('2014-03-20') - time()) / ( 60 * 60 * 24 ));
    //$sz1="<span class=\"kicsi\"><a href=\"http://terkep.miserend.hu\" target=\"_blank\">Már csak ".$marcsak." nap és itt a térkép.</a></span>";
    //$sz2= $sz1;
    ////////////////////////


    if (!empty($megjegyzes)) {
        global $design_url;
        $variables = array(
            'header' => array('content' => 'Jó tudni...'),
            'content' => nl2br($megjegyzes),
            'settings' => array('width=50%', 'align=right'),
            'design_url' => $design_url);
        $jotudni = $twig->render('doboz_lila.html', $variables);
    }

    //képek	
    $query = "select fajlnev,felirat from kepek where tid='$tid' order by sorszam";
    $lekerdez = mysql_query($query);
    $mennyi = mysql_num_rows($lekerdez);
    if ($mennyi > 0) {

        $scrollable .= '<script>
			$(document).ready(function(){                
                $("#my-als-list").als(	{visible_items: ';
        if ($mennyi < 4)
            $scrollable .= 4;
        else
            $scrollable .= 4;
        $scrollable .= '});                      
                $(".als-color").colorbox({rel:\'als-color\', transition:"fade",maxHeight:"98%"},
                    function() {
                        ga(\'send\',\'event\',\'Photos\',\'templom\',\'' . $tid . '\')        });            
                
             });
        </script>';

        $kepek.="\n";

        $kepek .= '<div class="als-container" id="my-als-list">
  <span class="als-prev"><img src="img/als/thin_left_arrow_333.png" alt="prev" title="previous" /></span>
  <div class="als-viewport">
    <ul class="als-wrapper">';

        $konyvtar = "kepek/templomok/$tid";
        while (list($fajlnev, $kepcim) = mysql_fetch_row($lekerdez)) {
            $altT[$fajlnev] = $kepcim;
            if (!isset($ogimage))
                $ogimage = '<meta property="og:image" content="' . $konyvtar . "/" . $fajlnev . '">';
            @$info = getimagesize("$konyvtar/kicsi/$fajlnev");
            $w1 = $info[0];
            $h1 = $info[1];
            if ($h1 > $w1 and $h1 > 90) {
                $arany = 90 / $h1;
                $ujh = 90;
                $ujw = $w1 * $arany;
            } else {
                $ujh = $h1;
                $ujw = $w1;
            }
            $osszw = $osszw + $ujw;
            $title = rawurlencode($kepcim);

            $kepek .= "<li class='als-item'><a href=\"$konyvtar/$fajlnev\" title=\"$title\" class='als-color'><img src=$konyvtar/kicsi/$fajlnev title='$kepcim' ></a></li>\n";
        }
        if ($mennyi < 4)
            for ($i = 0; $i < 4 - $mennyi; $i++)
                $kepek .= "<li class='als-item'></li>";

        $kepek.='</ul>
            </div>
            <span class="als-next"><img src="img/als/thin_right_arrow_333.png" alt="next" title="next" /></span>
            </div>';

        $kepek .= $scrollable;
        if (isset($ogimage))
            $meta .= $ogimage . "\n";
    }


    /*
      //Segíts a frissítésben!
      if(strtotime($frissitve) < strtotime("-3 year")) {
      session_start();
      if(!isset($_SESSION['help_'.$tid])) {
      $new = true;
      $_SESSION['help_'.$tid] = time();
      } else $new = false;
      $help = '
      <script>
      $(document).ready(function(){
      $.colorbox.settings.close = \'Bezár\';
      ';
      if($new == true) $help .= '$.colorbox({inline:true, href:"#inline_content",maxWidth:"70%"}, function () {
      ga(\'send\',\'event\',\'Update\',\'help2update\',\''.$tid.'\');
      });';

      $help .= '
      //Examples of how to assign the Colorbox event to elements
      $(".ajax").colorbox();
      $(".inline").colorbox({inline:true, width:"50%",maxWidth:"70%"}, function () {
      ga(\'send\',\'event\',\'Update\',\'help2updateRe\',\''.$tid.'\');
      });
      });

      </script>';



      $help .= '<!-- This contains the hidden content for inline calls -->
      <div style=\'display:none\'>
      <div id=\'inline_content\' style=\'padding:10px; background:#fff;\'>
      <div class="focim_fekete block" style="background-color: #D0D6E4;width:100%;margin-bottom:5px">
      <img src="'.$design_url.'/img/negyzet_lila.gif" width="6" height="8" align="absmiddle" style="margin-right:5px;margin-left:10px;">
      <span class="dobozfocim_fekete">Segítséget kérünk!</span>
      <div class="focim_fekete" style="float:right;margin-right:10px;height:7px;">&nbsp;
      <img src="'.$design_url.'/img/lilacsik.jpg" width="170" height="7" align="absmiddle">
      </div>
      </div>
      <p class="alap">A honlapunk önkéntesek munkájával jött létre és a látogatóink segítségével tartjuk naprakészen az információkat. Sajnos viszont a <strong>'.$nev.' ('.$varos.')</strong> adatai már régen voltak frissítve ('.date('Y.m.d.',strtotime($frissitve)).'). Ezért könnyen lehet, hogy hibás már a miserend.</p>';

      $results2 = mysql_query("SELECT * FROM eszrevetelek WHERE hol = 'templomok' AND hol_id = ".$tid." AND ( allapot = 'u' OR allapot = 'f' ) ORDER BY datum DESC, allapot DESC LIMIT 1 ;");
      if(mysql_num_rows($results2)>0) {
      $eszre = mysql_fetch_assoc($results2);
      $help .= '<p class="alap"><strong>Nagy örömünkre már volt olyan látogatónk, aki utána nézett az adatoknak. Éppen most dolgozzuk fel a beküldött észrevételt.</strong></p>';
      } else {
      $help .= '<p class="alap" align="center"><strong>Kérünk, csatlakozz a munkánkhoz és segíts a többieknek azzal, hogy megküldöd nekünk, hogy jó-e az itteni miserendet, ha sikerült utánajárni!</strong></p>
      <div style="background-color:#F8F4F6;margin-bottom:5px;width:100%">'.$kapcsolat.'</div>';
      }
      $help .= ' '.$eszrevetel.'
      </div>
      </div>';

      $eszrevetel .= '<p><a class=\'inline\' href="#inline_content">Segíts frissíteni!</a></p>';
      }
      else $help = '';
     */

    $result = mysql_query("SELECT id FROM favorites WHERE uid = " . $user->uid . " AND tid = " . $tid . " LIMIT 1");
    if (mysql_num_rows($result) == 1 AND $user->uid > 0)
        $favorite = 1;
    elseif ($user->uid > 0)
        $favorite = 0;
    else
        $favorite = -1;

    if ($vane > 0) {
        $variables = array(
            'tid' => $tid,
            'title' => $church['nev'] . " | Miserend",
            'nev' => $nev, 'ismertnev' => $ismertnev,
            'favorite' => $favorite,
            'varos' => $varos,
            'frissites' => $frissites,
            'nyari' => $nyari,
            'teli' => $teli,
            'miserend' => $misek,
            'eszrevetel' => $eszrevetel,
            'androidreklam' => androidreklam(),
            'kepek' => $kepek,
            'jotudni' => $jotudni,
            'leiras' => $leiras,
            'cim' => $cim,
            'terkep' => $terkep,
            'megkozelites' => nl2br($megkozelites),
            'kapcsolat' => $kapcsolat,
            'miseaktiv' => $miseaktiv,
            'misemegjegyzes' => nl2br($misemegj),
            'szomszedok' => $szomszedok,
            'napok' => array('', 'hétfő', 'kedd', 'szerda', 'csütörtök', 'péntek', 'szombat', '<font color=#AC282B><b>vasárnap</b></font>'),
            'design_url' => $design_url,
            'campaign' => updatesCampaign(),
            'alert' => LiturgicalDayAlert('html'),
        );
        $variables['template'] = 'church';
        $variables['scripts'] = $script;
        return $variables;
    } else {

        $kod = "<span class=hiba>A keresett templom nem található.</span>";

        return $kod;
    }
}

function androidreklam() {
    global $twig;
    $dobozcim = 'Már androidra is';
    //$dobozszoveg=nl2br($misemegj);
    $dobozszoveg = "<a href=\"https://play.google.com/store/apps/details?id=com.frama.miserend.hu\" onclick=\"ga('send','event','Advertisment','play.store','liladoboz-kep')\"><img src=\"http://terkep.miserend.hu/images/device-2014-03-24-230146_framed.png\" height=\"180\" style=\"float:right\"></a>Megjelent a <a href=\"https://play.google.com/store/apps/details?id=com.frama.miserend.hu\" onclick=\"ga('send','event','Advertisment','play.store','liladoboz')\">miserend androidos mobiltelefonokra</a> készült változata is. Ám még meg kell találni néhány templomnak a pontos helyét a térképen. Kérem segítsen nekünk!<br/><center><a href=\"http://terkep.miserend.hu\" onclick=\"ga('send','event','Advertisment','terkep.miserend.hu','liladoboz')\">terkep.miserend.hu</a></center>";

    $s = rand(1, 8);

    if ($s > 6)
        $dobozszoveg = "<a href=\"https://play.google.com/store/apps/details?id=com.frama.miserend.hu\"  onclick=\"ga('send','event','Advertisment','play.google.com','liladoboz-kep')\">
  <img alt=\"Töltd le a Google Play-ről\" src=\"img/hu_generic_rgb_wo_60.png\" style=\"display:block;margin-right:auto;margin-left:auto;width:100%;max-width:172px\" /></a>";
    else
        $dobozszoveg = '<center><a href="https://geo.itunes.apple.com/us/app/miserend/id967827488?mt=8" style="display:inline-block;overflow:hidden;background:url(http://linkmaker.itunes.apple.com/images/badges/en-us/badge_appstore-lrg.svg) no-repeat;width:165px;height:40px;" onclick="ga(\'send\',\'event\',\'Advertisment\',\'itunes.apple.com\',\'liladoboz-kep\')\"></a></center>';

    return $dobozszoveg;
}

function miserend_getRegi() {
    $return = array();
    $results = mysql_query('SELECT templomok.id, templomok.varos, templomok.nev, templomok.ismertnev, frissites, egyhazmegye, egyhazmegye.nev as egyhazmegyenev FROM templomok LEFT JOIN egyhazmegye ON egyhazmegye.id = egyhazmegye WHERE templomok.ok = "i" AND templomok.nev LIKE \'%templom%\' ORDER BY frissites ASC LIMIT 100');
    while ($templom = mysql_fetch_assoc($results)) {
        $results2 = mysql_query("SELECT * FROM eszrevetelek WHERE hol = 'templomok' AND hol_id = " . $templom['id'] . " AND ( allapot = 'u' OR allapot = 'f' ) ORDER BY datum DESC, allapot DESC LIMIT 1 ;");
        //while ($eszrevetel = mysql_fetch_assoc($results2)) { print_R($eszrevetel); }
        if (mysql_num_rows($results2) > 0) {
            $eszrevetel = mysql_fetch_assoc($results2);
            $templom['eszrevetel'] = $eszrevetel;
        }
        $return[] = $templom;
    }
    return $return;
}

function miserend_printRegi() {
    $templomok = miserend_getRegi();

    $return = '<img src="design/miserend/img/negyzet_kek.gif" align="absmiddle" style="margin-right:5px"><span class="dobozcim_fekete">Legrégebben frissített templomaink</span><br/>';
    $return .= "<span class=\"alap\">Segíts nekünk az adatok frissen tartásában! Hívj fel egy régen frissült templomot!</span><br/><br/>";
    $c = 0;
    foreach ($templomok as $templom) {
        if (isset($templom['eszrevetel'])) {
            $return .= "<span class=\"alap\"><i>folyamatban: " . $templom['nev'] . " (" . $templom['varos'] . ")</i></span><br/>\n";
        } else {
            $return .= "<span class=\"alap\">" . date('Y.m.d.', strtotime($templom['frissites'])) . "</span> <a class=\"felsomenulink\" href=\"?templom=" . $templom['id'] . "\">" . $templom['nev'] . " (" . $templom['varos'] . ")</a><br/>\n";
        }
        //echo print_R($templom,1)."<br>";

        if ($c > 10)
            break;
        $c++;
    }


    return $return;
}

function miserend_list($list) {

    return "?m_id=26&m_op=list&list=Balaton";
}

switch ($m_op) {
    case 'index':
        $tartalom = miserend_index();
        break;

    case 'map':
        $tartalom = map_view();
        break;

    case 'keres':
        if (isset($_REQUEST['misekereses']))
            $tartalom = miserend_misekeres();
        else
            $tartalom = miserend_templomkeres();
        break;


    case 'view':
        $tartalom = miserend_view();
        break;

    case 'list':
        $tartalom = miserend_list($_REQUEST['list']);
        break;
}
?>
