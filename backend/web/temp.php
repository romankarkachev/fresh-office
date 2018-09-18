<?php
$a;
error_reporting(0);
ini_set('display_errors','Off');
//ini_set('error_reporting', 0);
define('_CONSTS_','');
include_once(_CONSTS_.'consts.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/table-objects.php');

include_once($_SERVER['DOCUMENT_ROOT'].'/security/security.php');

$TITLE='АВК. Управление абонентами';
$NEED_GREETING=false;
$_SERVER['PHP_SELF']=$_SERVER['REQUEST_URI'];
// include_once($_SERVER['DOCUMENT_ROOT'].'/include/mycompany-functions.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/include/modules/subscribers_menu.php');

// echo '"'.getUsersOrganisation().'"';

if ($_SESSION['loggedin']!=1) {
    $output['text'] = 'Вам необходимо авторизироваться на сайте';
    $_SESSION['login']['returnPath']=$_SERVER['REQUEST_URI'];
    header('Location: /login/');
    die;
}

if ( (!isset($_SESSION['user_all']['allorganisations'][1])) or ($_SESSION['user_all']['allorganisations'][1]['orgApproved']!=1) ) {
    redirectOnErrorPage(403);
    die;
}

if (!isset($WEB_PERMISSIONS['subscribersManagement'])) {
    redirectOnErrorPage(403);
    die;
}

//print_r($_POST);



// read departures days for all organisations
$query='
          SELECT 
            d.type, 
            d.week, 
            d.day, 
            d.project,
            p.organisation
          FROM '.$TDepartures->name.' as d LEFT JOIN '.$TOrgProjects->name.' as p ON (d.project=p.ID)
          WHERE 
            d.type=1
          ORDER BY p.organisation, week, day
       ';
// echo $query;
$result=$TWebOrganisations->Query($query);
$plnDepDays=array();
if (mysql_num_rows($result)>0) {
    while ($l=mysql_fetch_array($result)) {
        $plnDepDays[$l['organisation']][$l['type']][]=$l;
    }
}

// read ammount of planed departures for all organisations
$defDeparturesArray=array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
$plnDepartures=array();
$query='
      SELECT 
            d.project, 
            d.type, 
            sum(if(d.type is null,0,1)) as ammount
      FROM '.$TOrgProjects->name.' as p LEFT JOIN '.$TDepartures->name.' as d ON (p.ID=d.project)
      WHERE 
        p.active=1
      GROUP BY p.ID, d.type
      ORDER BY p.ID, d.type
   ';

// echo $query;
$result=$TWebOrganisations->Query($query);
if (mysql_num_rows($result)>0) {
    while ($l=mysql_fetch_array($result)) {
        if (!isset($plnDepartures[$l['project']])) {
            $plnDepartures[$l['project']]=$defDeparturesArray;
        }
        // if ($l['type']==7) {$l['ammount']*=23;}
        if (!empty($l['type'])) {
            $plnDepartures[$l['project']][$l['type']]=$l['ammount'];
        }
    }
}

// print_r($plnDepartures);

// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// read planed departures for all organisations
/*
$aStart=mktime(0,0,0,date('m'),1,date('Y'));
$aFinish=mktime(23,59,59,date('m'),date('d'),date('Y'));
$curMonthPR=getAvgOrgsPR($aStart,$aFinish);

$aStart=mktime(0,0,0,date('m')-1,1,date('Y'));
$aFinish=mktime(23,59,59,date('m'),0,date('Y'));
$prevMonthPR=getAvgOrgsPR($aStart,$aFinish);

$aStart=0;
$aFinish=mktime(23,59,59,date('m'),date('d'),date('Y'));
$allPR=getAvgOrgsPR($aStart,$aFinish);
*/
// print_r($allPR);


// get params
if (empty($_GET['set'])) {
    $prjSet=0;
} else {
    $prjSet=intval($_GET['set']);
}

$menuActive='';
switch ($prjSet) {
    case 1:
        // $prjWhere='t.type in (1,2,9,5,3)';
        $prjWhere='tt.subscribersProject=1';
        $menuActive='set1';
        break;
    case 2:
        // $prjWhere='t.type in (11,10,4,7,6,8,12,13,14)';
        $prjWhere='tt.subscribersProject<>1';
        $menuActive='set2';
        break;
    default:
        $prjWhere=' 1 ';
}

if(!empty($_GET['type'])) {
    $prjWhere='t.type='.intval($_GET['type']).'';
    $menuActive='typed';
}









// read all projects and orgs IDs
$sums=array(
    'dep_rgl' => 0,
    'dep_pln' => 0,
    'dep_urg' => 0,
    'dep_prj' => 0,
    'dep_out' => 0,
    'servers' => 0,
    'awp_base' => 0,
    'awp_plan' => 0,
    'payment'  => 0,
);
$query='
    SELECT
        t.ID,
        t.organisation,
        t.type as type_original,
        tt.name as type,
        t.contract_awp,
        t.rgl_ammount,
        t.urg_ammount,
        t.prj_ammount,
        t.pln_ammount,
        t.out_ammount,
        t.tariff,
        t.price,
        
        1 as dump
    FROM
        '.$TOrgProjects->name.' as t LEFT JOIN '.$TOrgProjectsTypes->name.' as tt ON (t.type=tt.ID)
    WHERE
        t.active=1 and
        '.$prjWhere.'
    ORDER BY 
        t.organisation, tt.extOrder, tt.ID
 ';


$orgProjects=array(0);
$orgsAwpsContract=array();

$result=$TOrgProjects->Query($query);
if (mysql_num_rows($result)>0) {
    while ($l=mysql_fetch_array($result)) {
        $orgProjects[$l['organisation']][]=$l;
        if (!isset($plnDepartures[$l['ID']])) {
            $plnDepartures[$l['ID']]=$defDeparturesArray;
        }
        if ( (!isset($orgsAwpsContract[$l['organisation']])) or ($orgsAwpsContract[$l['organisation']]<$l['contract_awp']) ) {
            $orgsAwpsContract[$l['organisation']]=$l['contract_awp'];
        }

        $sums['dep_rgl']+=$l['rgl_ammount'];
        $sums['dep_pln']+=$l['pln_ammount'];
        $sums['dep_urg']+=$l['urg_ammount'];
        $sums['dep_out']+=$l['out_ammount'];
        $sums['dep_prj']+=$l['prj_ammount'];
        $sums['payment']+=$l['price'];
    }
}
$sums['awp_plan']+=array_sum($orgsAwpsContract);

// print_r($orgProjects[116]);



// get AWP ammount by organisations from infrastructure
$result=$TServices->Query('
                                 SELECT count(*), s.organisation
                                 FROM '.$TSAWP->name.' as s
                                 WHERE
                                    s.organisation in ('.implode(', ',array_keys($orgProjects)).') and
                                    s.active=1 and
                                    (substring(s.name,1,2)<>"&&" and substring(s.name,1,10)<>"&amp;&amp;")
                                 GROUP BY s.organisation
                                 ');
$awps=array();
if (mysql_num_rows($result)>0) {
    while ($line=mysql_fetch_array($result)) {
        if (!isset($awps[$line[1]])) {
            $awps[$line[1]]=0;
        }
        $awps[$line[1]]+=$line[0];
    }
    mysql_free_result($result);
}
// print_r($awps);


// get AWP ammount by organisations from infrastructure
$result=$TServices->Query('
                                 SELECT count(*), s.organisation
                                 FROM '.$TSAWP->name.' as s
                                 WHERE
                                    s.organisation in ('.implode(', ',array_keys($orgProjects)).') and
                                    s.active=1 and
                                    (substring(s.name,1,2)<>"&&" and substring(s.name,1,10)<>"&amp;&amp;")
                                 GROUP BY s.organisation
                                 ');
$awps=array();
if (mysql_num_rows($result)>0) {
    while ($line=mysql_fetch_array($result)) {
        if (!isset($awps[$line[1]])) {
            $awps[$line[1]]=0;
        }
        $awps[$line[1]]+=$line[0];
    }
    mysql_free_result($result);
}
// print_r($awps);



// read servers ammount for selected organisations
$query='
    SELECT
        wl.PID,
        wl.UID,
        CONCAT(u.lastname,"&nbsp;",SUBSTR(u.name,1,1),".",SUBSTR(u.secname,1,1),".") as user_fio
    FROM '.$TWebOrganisationDEWhiteList->name.' as wl 
                JOIN '.$TWebUsers->name.' as u ON (wl.UID=u.ID)
    WHERE
        wl.PID in ('.implode(', ',array_keys($orgProjects)).')
    
 ';
$res=$TSAWP->Query($query);
$orgWhiteList=$TSAWP->getAllResults($res,'PID',false);
// print_r($orgWhiteList);



// read contact organisations users
// read used organisations workers (contactlist)
$orgUsers=array();
$orgUsers_txt=array();
$result=$TWebOrganisations->Query('
      SELECT  
        u.ID, u.lastname, u.name, u.secname, u.login, uo.OID as organisation, u.icq, u.phone, u.email, u.bDate, u.canApprove,
        j.name as job
        
      FROM '.$TWebUsers->name.' as u 
                                     LEFT JOIN '.$TJobs->name.' as j ON (u.job=j.ID) 
                                     LEFT JOIN '.$TWebUsers2Organisations->name.' as uo ON (uo.UID=u.ID)
                                     LEFT JOIN '.$TWebOrganisations->name.' as o ON (uo.OID=o.ID)
      WHERE
           uo.orgApproved=1 and
           o.ID in ('.implode(', ',array_keys($orgProjects)).') and
           u.active=1 and u.activated=1 and uo.fired = 0 and uo.orgApproved=1
      ORDER BY organisation, lastname, name
   ');
if (mysql_num_rows($result)>0) {
    while ($l=mysql_fetch_array($result)) {
        if (!isset($orgUsers[$l['organisation']])) {
            $orgUsers[$l['organisation']]=array();
        }
        $orgUsers[$l['organisation']][]=$l;
        $orgUsers_txt[$l['organisation']][]='
                    <TR style="'.(($l['canApprove']>0)?'background: #AAFFAA; font-weight: 600;':'').' padding-bottom: 4px;">
                            <TD>'.count($orgUsers[$l['organisation']]).'.</TD>
                            <TD>'.$l['lastname'].' '.$l['name'].' '.$l['secname'].'</TD>
                            <TD><A HREF="mailto:'.$l['email'].'">'.$l['email'].'</A></TD>
                            <TD>'.((!empty($l['job']))?''.$l['job']:'').'</TD>
                            <TD>'.str_replace(' ','&nbsp;',$l['phone']).'</TD>
                            <TD>'.((!empty($l['icq']))?'ICQ:'.$l['icq']:'').'</TD>
                    </TR>';
        /*

                   <TR style="'.(($l['canApprove']>0)?'background: #AAFFAA; font-weight: 600;':'').' padding-bottom: 4px;">
                       <TD>'.$l['lastname'].' '.$l['name'].' '.$l['secname'].'</TD>
                       <TD>(<A HREF="mailto:'.$l['email'].'">'.$l['login'].'</A>)</TD>
                       <TD>'.(($l['bDate']!='0000-00-00')?$l['bDate']:'&nbsp;').'</TD>
                       <TD>'.str_replace(' ','&nbsp;',$l['phone']).'</TD>
                       <TD>'.((!empty($l['icq']))?'ICQ:'.$l['icq']:'').'</TD>
                       '.((isset($WEB_PERMISSIONS['manageClientsUsers']))?'<TD><A target=_blank HREF="/user/'.$l['ID'].'/edit/" onClick="OpenNewSized(this.href, 960,600); return(false);">Edit</A></TD>':'').'
                   </TR>';
                       <TD>'.(($l['bDate']!='0000-00-00')?$l['bDate']:'&nbsp;').'</TD>
                       <TD>'.str_replace(' ','&nbsp;',$l['phone']).'</TD>
                       <TD>'.((!empty($l['icq']))?'ICQ:'.$l['icq']:'').'</TD>
        */
    }
}









// read data about main organisations (non-branches)
$query='
      SELECT
         o.ID as ID,
         IF(web_organisations_forms.sName is null,"Не определено",web_organisations_forms.sName) as lawForm,
         o.lawForm as lawForm_original,
         o.title as title,
         o.phones as phones,
         o.quick_dial_phone as quick_dial_phone,
         o.sTitle as sTitle,

         o.lastAction,
         o.active as active,
         o.findex, o.fcity, o.fstreet, o.fhouse, o.fappartment, o.fbuilding, o.foffice, o.fflat, o.ffloor,
         m.name as metro,

         IF(o2.sTitle is null,"Не определено",o2.sTitle) as branch,
         o.branch as branch_original,
         o.wdStart as wdStart,
         o.wdFinish as wdFinish,
         
         if (u.ID is null,"'.$TWebOrganisations->columns[$TWebOrganisations->colIDs['manager']]->showEmptyVariantText.'",CONCAT(u.lastname,"&nbsp;",SUBSTR(u.name,1,1),".",SUBSTR(u.secname,1,1),".")) as manager_fio,
         if (u2.ID is null,"'.$TWebOrganisations->columns[$TWebOrganisations->colIDs['techmanager']]->showEmptyVariantText.'",CONCAT(u2.lastname,"&nbsp;",SUBSTR(u2.name,1,1),".",SUBSTR(u2.secname,1,1),".")) as techmanager_fio,

         max(t.contract_awp) as awp_count,
         t.tariff as tariff,
         
         -- all this from projects
         sum(t.price) as price_sum,
         sum(t.rgl_ammount) as rgl_ammount_sum,
         sum(t.pln_ammount) as pln_ammount_sum,
         sum(t.urg_ammount) as urg_ammount_sum,
         sum(t.prj_ammount) as prj_ammount_sum,
         sum(t.out_ammount) as out_ammount_sum,
         
         1 as dumb
      FROM '.$TWebOrganisations->name.' as o
                            JOIN '.$TOrgProjects->name.' as t ON (t.organisation=o.ID)
                            JOIN '.$TOrgProjectsTypes->name.' as tt ON (t.type=tt.ID and '.$prjWhere.')
                            LEFT JOIN web_organisations_forms as web_organisations_forms ON (o.lawForm=web_organisations_forms.ID)
                            LEFT JOIN web_organisations as o2 ON (o.branch=o2.ID)
                            LEFT JOIN '.$TWebOrganisationsMetro->name.' as m ON (o.metro=m.ID)
                            LEFT JOIN '.$TWebUsers->name.' as u ON (o.manager=u.ID)
                            LEFT JOIN '.$TWebUsers->name.' as u2 ON (o.techmanager=u2.ID)
                            -- LEFT JOIN '.$TWebOrganisationTariffs->name.' as t ON (o.tariff=t.ID)
      WHERE
           o.ID in ('.implode(', ',array_keys($orgProjects)).') and
           t.active=1 and
           o.branch=0
      GROUP BY o.ID
      ORDER BY o.sTitle
      ';
//echo '<pre>';
//echo $query;
$orgs=array();
$orgs_index=array();
$orgs_all=array();
$result=$TWebOrganisations->Query($query);
if (mysql_num_rows($result)>0) {
    while ($l=mysql_fetch_array($result)) {
        $orgs_all[$l['ID']]=$l;
        $orgs[]=array(
            'main'     => $l['ID'],
            'branches' => array(),
            'branches_sums' => array(
                'dep_rgl' => 0,
                'dep_pln' => 0,
                'dep_urg' => 0,
                'dep_prj' => 0,
                'dep_out' => 0,
                'servers' => 0,
                'awp_plan' => 0,
                'awp_base' => 0,
                'payment' => 0,
            ),
        );
        $orgs_index[$l['ID']]=count($orgs)-1;
        /*
        if (!isset($plnDepartures[$l['ID']])) {
              $plnDepartures[$l['ID']]=$defDeparturesArray;
        }
        */

        $sums['servers']+=@$serversAmmount[$l['ID']]['ammount'];
    }
    mysql_free_result($result);
}
// $sums['awp_base']=array_sum($awps);






// read data about branches
$query='
      SELECT
         o.ID as ID,
         IF(web_organisations_forms.sName is null,"Не определено",web_organisations_forms.sName) as lawForm,
         o.lawForm as lawForm_original,
         o.title as title,
         o.phones as phones,
         o.quick_dial_phone as quick_dial_phone,
         o.sTitle as sTitle,

         o.lastAction,
         o.active as active,
         o.findex, o.fcity, o.fstreet, o.fhouse, o.fappartment, o.fbuilding, o.foffice, o.fflat, o.ffloor,
         m.name as metro,

         IF(o2.sTitle is null,"Не определено",o2.sTitle) as branch,
         o.branch as branch_original,
         o.wdStart as wdStart,
         o.wdFinish as wdFinish,

         if (u.ID is null,"'.$TWebOrganisations->columns[$TWebOrganisations->colIDs['manager']]->showEmptyVariantText.'",CONCAT(u.lastname,"&nbsp;",SUBSTR(u.name,1,1),".",SUBSTR(u.secname,1,1),".")) as manager_fio,
         if (u2.ID is null,"'.$TWebOrganisations->columns[$TWebOrganisations->colIDs['techmanager']]->showEmptyVariantText.'",CONCAT(u2.lastname,"&nbsp;",SUBSTR(u2.name,1,1),".",SUBSTR(u2.secname,1,1),".")) as techmanager_fio,

         max(t.contract_awp) as awp_count,
         t.tariff as tariff,
         
         -- all this from projects
         sum(t.price) as price_sum,
         sum(t.rgl_ammount) as rgl_ammount_sum,
         sum(t.pln_ammount) as pln_ammount_sum,
         sum(t.urg_ammount) as urg_ammount_sum,
         sum(t.prj_ammount) as prj_ammount_sum,
         sum(t.out_ammount) as out_ammount_sum,
         
         1 as dumb
      FROM '.$TWebOrganisations->name.' as o
                            JOIN '.$TOrgProjects->name.' as t ON (t.organisation=o.ID)
                            JOIN '.$TOrgProjectsTypes->name.' as tt ON (t.type=tt.ID and '.$prjWhere.')
                            LEFT JOIN web_organisations_forms as web_organisations_forms ON (o.lawForm=web_organisations_forms.ID)
                            LEFT JOIN web_organisations as o2 ON (o.branch=o2.ID)
                            LEFT JOIN '.$TWebOrganisationsMetro->name.' as m ON (o.metro=m.ID)
                            LEFT JOIN '.$TWebUsers->name.' as u ON (o.manager=u.ID)
                            LEFT JOIN '.$TWebUsers->name.' as u2 ON (o.techmanager=u2.ID)
                            -- LEFT JOIN '.$TWebOrganisationTariffs->name.' as t ON (o.tariff=t.ID)
      WHERE
           t.active=1 and
           o.ID in ('.implode(', ',array_keys($orgProjects)).') and
           o.branch<>0
      GROUP BY o.ID
      ORDER BY o.title
      ';
$result=$TWebOrganisations->Query($query);
if (mysql_num_rows($result)>0) {
    while ($l=mysql_fetch_array($result)) {
        $orgs_all[$l['ID']]=$l;
        if (isset($orgs_index[$l['branch_original']])) {
            $orgs[$orgs_index[$l['branch_original']]]['branches'][]=$l['ID'];

            $orgs[$orgs_index[$l['branch_original']]]['branches_sums']['dep_rgl']+=$l['rgl_ammount_sum'];
            $orgs[$orgs_index[$l['branch_original']]]['branches_sums']['dep_pln']+=$l['pln_ammount_sum'];
            $orgs[$orgs_index[$l['branch_original']]]['branches_sums']['dep_urg']+=$l['urg_ammount_sum'];
            $orgs[$orgs_index[$l['branch_original']]]['branches_sums']['dep_prj']+=$l['prj_ammount_sum'];
            $orgs[$orgs_index[$l['branch_original']]]['branches_sums']['dep_out']+=$l['out_ammount_sum'];
            $orgs[$orgs_index[$l['branch_original']]]['branches_sums']['servers']+=@$serversAmmount[$l['ID']]['ammount'];
            $orgs[$orgs_index[$l['branch_original']]]['branches_sums']['awp_plan']+=$l['awp_count'];
            $orgs[$orgs_index[$l['branch_original']]]['branches_sums']['awp_base']+=@$awps[$l['ID']];
            $orgs[$orgs_index[$l['branch_original']]]['branches_sums']['payment']+=$l['price_sum'];

            /*
            if (!isset($plnDepartures[$l['ID']])) {
                  $plnDepartures[$l['ID']]=$defDeparturesArray;
            }

            $sums['dep_rgl']+=$l['rgl_ammount_sum'];
            $sums['dep_pln']+=$l['pln_ammount_sum'];
            $sums['dep_urg']+=$l['urg_ammount_sum'];
            $sums['dep_out']+=$l['out_ammount_sum'];
            */
            $sums['awp_plan']+=$l['awp_count'];
            // $sums['payment']+=$l['price_sum'];
            $sums['servers']+=@$serversAmmount[$l['ID']]['ammount'];
        }
    }
    mysql_free_result($result);
}

// print_r($plnDepartures);
// print_r($orgs);





///////////////////////// OUTPUT /////////////////////////

$output['text']='
              <TABLE cellspacing=0 cellpadding=4 border=1>
              <TR>
                  <TD align=center rowspan=2>N</TD>
                  <TD align=center rowspan=2>Название</TD>
                  <TD align=center rowspan=2>Мастер</TD>
                  <TD align=center rowspan=2>Тех.поддержка</TD>
                  <TD align=center rowspan=2>Менеджер</TD>
                  <TD align=center rowspan=2>Телефон</TD>
                  <TD align=center rowspan=2>График</TD>
                  <TD align=center rowspan=2>Метро</TD>
                  <TD align=center rowspan=2>Адрес</TD>
                  <TD align=center rowspan=2>Тип проекта</TD>
                  <TD align=center colspan=5>Выезды</TD>
                  <TD align=center rowspan=2>Серверов</TD>
                  <TD align=center rowspan=2>АРМ (договор)</TD>
                  <TD align=center rowspan=2>АРМ (в базе)</TD>
                  <TD align=center rowspan=2>Стоимость по договору</TD>
              </TR>
              <TR>
                  <TD align=center onMouseOver="show(\'rgl_notice\');" onMouseOut="hide(\'rgl_notice\');"><A HREF="/istruct/departures/table/" onClick="OpenNewSized(this.href, 400,400); return(false);">RGL</A>'.genBox('rgl_notice','по договору/запланировано','width: 200px; top: 0px; ',false).'</TD>
                  <TD align=center onMouseOver="show(\'pln_notice\');" onMouseOut="hide(\'pln_notice\');">PLN'.genBox('pln_notice','по договору/запланировано','width: 200px; top: 0px; ',false).'</TD>
                  <TD align=center onMouseOver="show(\'urg_notice\');" onMouseOut="hide(\'urg_notice\');">URG'.genBox('urg_notice','по договору/запланировано','width: 200px; top: 0px; ',false).'</TD>
                  <TD align=center onMouseOver="show(\'prj_notice\');" onMouseOut="hide(\'prj_notice\');">PRJ'.genBox('prj_notice','по договору/запланировано','width: 200px; top: 0px; ',false).'</TD>
                  <TD align=center onMouseOver="show(\'out_notice\');" onMouseOut="hide(\'out_notice\');">OUT'.genBox('out_notice','по договору/запланировано','width: 200px; top: 0px; ',false).'</TD>
              </TR>
 ';

// print_r($orgProjects[116]);
reset($orgs);
$recNumber=0;
while (list(,$v)=each($orgs)) {
    $recNumber++;
    /*
          <A style="color: #777777;" HREF="#" onClick="switchDIV(\'contlist_'.$aValue['ID'].'\'); return(false);">Контактные лица</A>
               '.genBox('contlist_'.$aValue['ID'],'
                    '.$contListContent.'
               ','text-align: left; width: 600px; left: -100px; top: 10px;',true).'
          ':'').'
    */
    // output main
    $aValue=$orgs_all[$v['main']];
    $contContent=''.((isset($orgUsers_txt[$aValue['ID']]))?'<TABLE cellspacing=0 cellpadding=2 border=0>'.implode($orgUsers_txt[$aValue['ID']]).'</TABLE>':'').'
                      '.((isset($WEB_PERMISSIONS['manageClientsUsers']))?"\n".'<DIV style="text-align: center; padding-top: 10px;"><A targe=_blank HREF="/users/add/org'.$aValue['ID'].'/" onClick="OpenNewSized(this.href, 960,600); return(false);">Добавить пользователя</A></DIV>':'').'
                      ';

    $fAddress=array(
        $aValue['findex'],
        ((!empty($aValue['fcity']))?'г.'.$aValue['fcity']:''),
        ((!empty($aValue['fhouse']))?'ул. '.$aValue['fstreet']:''),
        ((!empty($aValue['fhouse']))?'д. '.$aValue['fhouse']:''),
        ((!empty($aValue['fbuilding']))?'корп. '.$aValue['fbuilding']:''),
        ((!empty($aValue['fappartment']))?'стр. '.$aValue['fappartment']:''),
        ((!empty($aValue['ffloor']))?'эт. '.$aValue['ffloor']:''),
        ((!empty($aValue['foffice']))?'оф. '.$aValue['foffice']:''),
        ((!empty($aValue['fflat']))?'кв. '.$aValue['fflat']:''),
    );

    $rglOut = array();
    for ($i=0; $i < count(@$plnDepDays[$aValue['ID']][1]); $i++) {
        $rglOut[$plnDepDays[$aValue['ID']][1][$i]['project']][] = $plnDepDays[$aValue['ID']][1][$i]['week'] . '-' . $TDepartures->columns[$TDepartures->colIDs['day']]->variants[$plnDepDays[$aValue['ID']][1][$i]['day']];
    }
//***
    foreach($rglOut as $key => $item) {
        $rglOut[$key] = implode(', ', $rglOut[$key]);
    }

    // get projects information ready
    $projects=array();
    $curOrgPrjCount=count($orgProjects[$aValue['ID']]);
    $curOrgAWPInBaseSum=0;

    // get whitelist executors list
    $aValue['wl_executors']=array();
    if (isset($orgWhiteList[$aValue['ID']])) {
        for($i=0;$i<count($orgWhiteList[$aValue['ID']]);$i++) {
            $aValue['wl_executors'][]=$orgWhiteList[$aValue['ID']][$i]['user_fio'];
        }
    }

    for ($i=0;$i<$curOrgPrjCount;$i++) {
        $pValue=$orgProjects[$aValue['ID']][$i];

        // get org AWPs
        $contract_awp_color='#000000';
        $contract_awp_ammount=sprintf('%d',@$GLOBALS['awps'][$pValue['organisation']]);

        if ($pValue['contract_awp']==0) {
            $contract_awp_ammount=0;
        } else {
            if ($pValue['contract_awp']!=@$GLOBALS['awps'][$pValue['organisation']]) {
                $contract_awp_color='#FF0000';
            }
        }
        $curOrgAWPInBaseSum+=$contract_awp_ammount;


        $projects[]='
                  <TD><span title="Номер проекта">№'.$pValue['ID'].'</span> <A HREF="/companies/'.$aValue['ID'].'/projects/">"'.$pValue['type'].'"</A></TD>

                  <TD _valign=top align=center '.((@$plnDepartures[$pValue['ID']][1]==@$pValue['rgl_ammount'])?'':'style="background: #FFAAAA;"').'>
                    '.(($pValue['rgl_ammount']+@$plnDepartures[$pValue['ID']][1]>0)
                ? //***
                '<br><br><SPAN onMouseOver="show(\'rgl_'.$pValue['ID'].'\');" onMouseOut="hide(\'rgl_'.$pValue['ID'].'\');">'.$pValue['rgl_ammount'].'/'.@$plnDepartures[$pValue['ID']][1].'</SPAN>'.
                genBox('rgl_'.$pValue['ID'].'',$rglOut[$pValue['ID']],'width: 150px; top: 0px; left: -75px;',false)
                :'&nbsp;').'
                  </TD>
                  <TD _valign=top align=center '.(($plnDepartures[$pValue['ID']][2]==$pValue['pln_ammount'])?'':'style="background: #FFAAAA;"').'>'.(($pValue['pln_ammount']+@$plnDepartures[$pValue['ID']][2]>0)?$pValue['pln_ammount'].'/'.@$plnDepartures[$pValue['ID']][2]:'&nbsp;').'</TD>
                  <TD _valign=top align=center>'.(($pValue['urg_ammount']+$plnDepartures[$pValue['ID']][3]>0)?$pValue['urg_ammount'].'/'.@$plnDepartures[$pValue['ID']][3]:'&nbsp;').'</TD>
                  <TD _valign=top align=center>'.(($pValue['prj_ammount']+$plnDepartures[$pValue['ID']][6]>0)?$pValue['prj_ammount'].'/'.@$plnDepartures[$pValue['ID']][6]:'&nbsp;').'</TD>
                  <TD _valign=top align=center '.((@$plnDepartures[$pValue['ID']][7]==@$pValue['out_ammount'])?'':'style="background: #FFAAAA;"').'>'.(($pValue['out_ammount']+@$plnDepartures[$pValue['ID']][7]>0)?$pValue['out_ammount'].'/'.@$plnDepartures[$pValue['ID']][7]:'&nbsp;').'</TD>

                  <TD _valign=top align=center>'.( (!empty($serversAmmount[$aValue['ID']]['ammount']))?$serversAmmount[$aValue['ID']]['ammount']:0).'</TD>
                  <TD _valign=top align=center>'.$pValue['contract_awp'].'</TD>
                  <TD _valign=top align=center style="color: '.$contract_awp_color.';">'.$contract_awp_ammount.'</TD>
                  <TD _valign=top align=center>'.$pValue['price'].'</TD>
            ';
    }

//                       <TD _valign=top rowspan='.$curOrgPrjCount.' align=center>
//                    <SPAN onMouseOver="show(\'wlexecutors_'.$aValue['ID'].'\');" onMouseOut="hide(\'wlexecutors_'.$aValue['ID'].'\');">'.$aValue['manager_fio'].'/'.$aValue['techmanager_fio'].'</SPAN>
//                    '.((!empty($aValue['wl_executors']))?''.genBox('wlexecutors_'.$aValue['ID'],'<B>Закрепленные мастера</B>:<BR>'.implode(', ',$aValue['wl_executors']).'','text-align: left; width: 200px; left: 0px; top: 10px;',false):'').'
//              </TD>


    $output['text'].='
           <TR>
              <TD _valign=top rowspan='.$curOrgPrjCount.' align=center>'.$recNumber.'</TD>
              <TD _valign=top rowspan='.$curOrgPrjCount.'>
                  <A HREF="/companies/'.$aValue['ID'].'/">'.$aValue['sTitle'].'</A>
                      '.((!empty($contContent))
            ?   '<A style="color: #777777;" HREF="#" onClick="switchDIV(\'contlist_'.$aValue['ID'].'\'); return(false);">контакты</A>
                          '.genBox('contlist_'.$aValue['ID'],'
                              '.$contContent.'
                          ','text-align: left; width: 700px; left: 0px; top: 10px;',true).''
            :'').'
              </TD>
              
              <TD _valign=top rowspan='.$curOrgPrjCount.' align=center>
                    '.((!empty($aValue['wl_executors']))
            ? implode(', ',$aValue['wl_executors'])
            :'Не определено').'
              </TD>
              <TD _valign=top rowspan='.$curOrgPrjCount.' align=center>
              '.$aValue['techmanager_fio'].'
              </TD>
              <TD _valign=top rowspan='.$curOrgPrjCount.' align=center>
              '.$aValue['manager_fio'].'
              </TD>
              
              <TD align=left rowspan='.$curOrgPrjCount.'>
                      <DIV style="color: #777777;">'.((!empty($aValue['quick_dial_phone']))?'['.$aValue['quick_dial_phone'].'] ':'').$aValue['phones'].'&nbsp;</DIV>
              </TD>
              <TD _valign=top rowspan='.$curOrgPrjCount.'>'.(($aValue['wdStart']!='00:00:00')?substr($aValue['wdStart'],0,5).'-'.substr($aValue['wdFinish'],0,5):'&nbsp;').'</TD>
              <TD _valign=top rowspan='.$curOrgPrjCount.' align=left>'.$aValue['metro'].'&nbsp;</TD>
              <TD _valign=top rowspan='.$curOrgPrjCount.' align=left>
                             '.
        implode(', ',array_filter($fAddress)).
        '&nbsp;
              </TD> 
              
              '.implode('</TR><TR>',$projects).'

           </TR>
        ';

    // output branches if any!
    if (count($v['branches'])>0) {
        $oldValue=$aValue;
        $brPRSum=0;
        $branchesPRAmmount=0;
        // echo "\n\n".$aValue['ID']."<BR><BR>\n\n";
        for ($ii=0;$ii<count($v['branches']);$ii++) {
            $recNumber++;
            $aValue=$orgs_all[$v['branches'][$ii]];
            if (isset($allPR[$aValue['ID']])) {
                $brPRSum+=$allPR[$aValue['ID']]['allAvg']['pr'];
                $branchesPRAmmount+=$allPR[$aValue['ID']]['allAvg']['ammount'];
            }
            $contContent=''.((isset($orgUsers_txt[$aValue['ID']]))?'<TABLE cellspacing=0 cellpadding=2 border=0>'.implode($orgUsers_txt[$aValue['ID']]).'</TABLE>':'').'
                          '.((isset($WEB_PERMISSIONS['manageClientsUsers']))?"\n".'<DIV style="text-align: center; padding-top: 10px;"><A targe=_blank HREF="/users/add/org'.$aValue['ID'].'/" onClick="OpenNewSized(this.href, 960,600); return(false);">Добавить пользователя</A></DIV>':'').'
                          ';
            $fAddress=array(
                $aValue['findex'],
                ((!empty($aValue['fcity']))?'г.'.$aValue['fcity']:''),
                ((!empty($aValue['fhouse']))?'ул. '.$aValue['fstreet']:''),
                ((!empty($aValue['fhouse']))?'д. '.$aValue['fhouse']:''),
                ((!empty($aValue['fbuilding']))?'корп. '.$aValue['fbuilding']:''),
                ((!empty($aValue['fappartment']))?'стр. '.$aValue['fappartment']:''),
                ((!empty($aValue['ffloor']))?'эт. '.$aValue['ffloor']:''),
                ((!empty($aValue['fflat']))?'кв. '.$aValue['fflat']:''),
                ((!empty($aValue['foffice']))?'оф. '.$aValue['foffice']:''),
            );

            $rglOut=array();
            for ($j=0;$j<count(@$plnDepDays[$aValue['ID']][1]);$j++) {
                $rglOut[]=$plnDepDays[$aValue['ID']][1][$j]['week'].'-'.$TDepartures->columns[$TDepartures->colIDs['day']]->variants[$plnDepDays[$aValue['ID']][1][$j]['day']];
            }
            $rglOut=implode(', ',$rglOut);

            // get whitelist executors list
            $aValue['wl_executors']=array();
            if (isset($orgWhiteList[$aValue['ID']])) {
                for($i=0;$i<count($orgWhiteList[$aValue['ID']]);$i++) {
                    $aValue['wl_executors'][]=$orgWhiteList[$aValue['ID']][$i]['user_fio'];
                }
            }
            // get projects information ready
            $projects=array();
            $curOrgPrjCount=count($orgProjects[$aValue['ID']]);
            for ($i=0;$i<$curOrgPrjCount;$i++) {
                $pValue=$orgProjects[$aValue['ID']][$i];
                $curOrgAWPInBaseSum+=@$GLOBALS['awps'][$pValue['organisation']];
                $projects[]='
                     <TD>'.$pValue['type'].'&nbsp;</TD>

                      <TD _valign=top align=center '.((@$plnDepartures[$pValue['ID']][1]==@$pValue['rgl_ammount'])?'':'style="background: #FFAAAA;"').'>
                        '.(($pValue['rgl_ammount']+@$plnDepartures[$pValue['ID']][1]>0)
                        ?
                        '<SPAN onMouseOver="show(\'rgl_'.$pValue['ID'].'\');" onMouseOut="hide(\'rgl_'.$pValue['ID'].'\');">'.$pValue['rgl_ammount'].'/'.@$plnDepartures[$pValue['ID']][1].'</SPAN>'.
                        genBox('rgl_'.$pValue['ID'].'',$rglOut,'width: 150px; top: 0px; left: -75px;',false)
                        :'&nbsp;').'
                      </TD>
                      <TD _valign=top align=center '.(($plnDepartures[$pValue['ID']][2]==$pValue['pln_ammount'])?'':'style="background: #FFAAAA;"').'>'.(($pValue['pln_ammount']+@$plnDepartures[$pValue['ID']][2]>0)?$pValue['pln_ammount'].'/'.@$plnDepartures[$pValue['ID']][2]:'&nbsp;').'</TD>
                      <TD _valign=top align=center>'.(($pValue['urg_ammount']+$plnDepartures[$pValue['ID']][3]>0)?$pValue['urg_ammount'].'/'.@$plnDepartures[$pValue['ID']][3]:'&nbsp;').'</TD>
                      <TD _valign=top align=center>'.(($pValue['prj_ammount']+$plnDepartures[$pValue['ID']][6]>0)?$pValue['prj_ammount'].'/'.@$plnDepartures[$pValue['ID']][6]:'&nbsp;').'</TD>
                      <TD _valign=top align=center '.((@$plnDepartures[$pValue['ID']][7]==@$pValue['out_ammount'])?'':'style="background: #FFAAAA;"').'>'.(($pValue['out_ammount']+@$plnDepartures[$pValue['ID']][7]>0)?$pValue['out_ammount'].'/'.@$plnDepartures[$pValue['ID']][7]:'&nbsp;').'</TD>

                       <TD _valign=top align=center>'.( (!empty($serversAmmount[$aValue['ID']]['ammount']))?$serversAmmount[$aValue['ID']]['ammount']:0).'</TD>
                       <TD _valign=top align=center>'.$pValue['contract_awp'].'</TD>
                      <TD _valign=top align=center '.(($aValue['awp_count']!=@$GLOBALS['awps'][$pValue['organisation']])?'style="color: #FF0000;"':'').'>'.((!empty($GLOBALS['awps'][$pValue['organisation']]))?$GLOBALS['awps'][$pValue['organisation']]:0).'</TD>
                      <TD _valign=top align=center>'.$pValue['price'].'</TD>
                ';
            }

//                                 <SPAN onMouseOver="show(\'wlexecutors_'.$aValue['ID'].'\');" onMouseOut="hide(\'wlexecutors_'.$aValue['ID'].'\');">'.$aValue['manager_fio'].'/'.$aValue['techmanager_fio'].'</SPAN>
//                    '.((!empty($aValue['wl_executors']))?''.genBox('wlexecutors_'.$aValue['ID'],'<B>Закрепленные мастера</B>:<BR>'.implode(', ',$aValue['wl_executors']).'','text-align: left; width: 200px; left: 0px; top: 10px;',false):'').'

            $output['text'].='
               <TR>
                  <TD _valign=top rowspan='.$curOrgPrjCount.' align=center>'.$recNumber.'</TD>
                  <TD _valign=top rowspan='.$curOrgPrjCount.' style="padding-left: 20px;">
                      <A style="color: #333333;" HREF="/companies/'.$aValue['ID'].'/">'.$aValue['title'].'</A>
                      '.((!empty($contContent))
                    ?   '<A style="color: #777777;" HREF="#" onClick="switchDIV(\'contlist_'.$aValue['ID'].'\'); return(false);">контакты</A>
                          '.genBox('contlist_'.$aValue['ID'],'
                              '.$contContent.'
                          ','text-align: left; width: 600px; left: 0px; top: 10px;',true).''
                    :'').'
                  </TD>
                  <TD _valign=top rowspan='.$curOrgPrjCount.' align=center>
                  '.((!empty($aValue['wl_executors']))
                    ? implode(', ',$aValue['wl_executors'])
                    :'Не определено').'
                  </TD>
                  <TD _valign=top rowspan='.$curOrgPrjCount.' align=center>
                  '.$aValue['techmanager_fio'].'
                  </TD>
                  <TD _valign=top rowspan='.$curOrgPrjCount.' align=center>
                  '.$aValue['manager_fio'].'
                  </TD>
                  <TD rowspan='.$curOrgPrjCount.' align=left>
                          <DIV style="color: #777777;">'.$aValue['phones'].'&nbsp;</DIV>
                  </TD>
                  <TD rowspan='.$curOrgPrjCount.'>'.(($aValue['wdStart']!='00:00:00')?substr($aValue['wdStart'],0,5).'-'.substr($aValue['wdFinish'],0,5):'&nbsp;').'</TD>
                  <TD rowspan='.$curOrgPrjCount.' align=left>'.$aValue['metro'].'&nbsp;</TD>
                  <TD _valign=top rowspan='.$curOrgPrjCount.' align=left>
                                 '.
                implode(', ',array_filter($fAddress)).
                '&nbsp;
                  </TD> 

                  '.implode('</TR><TR>',$projects).'

               </TR>
            ';
        } // for

        $aValue=$oldValue;
        $output['text'].='
           <TR>
              <TD _valign=top colspan=10 align=right>Суммарно по филиалам:</TD>
              <TD _valign=top align=center>'.(($v['branches_sums']['dep_rgl']+$aValue['rgl_ammount_sum']>0)?($v['branches_sums']['dep_rgl']+$aValue['rgl_ammount_sum']):'&nbsp;').'</TD>
              <TD _valign=top align=center>'.(($v['branches_sums']['dep_pln']+$aValue['pln_ammount_sum']>0)?($v['branches_sums']['dep_pln']+$aValue['pln_ammount_sum']):'&nbsp;').'</TD>
              <TD _valign=top align=center>'.(($v['branches_sums']['dep_urg']+$aValue['urg_ammount_sum']>0)?($v['branches_sums']['dep_urg']+$aValue['urg_ammount_sum']):'&nbsp;').'</TD>
              <TD _valign=top align=center>'.(($v['branches_sums']['dep_prj']+$aValue['prj_ammount_sum']>0)?($v['branches_sums']['dep_prj']+$aValue['prj_ammount_sum']):'&nbsp;').'</TD>
              <TD _valign=top align=center>'.(($v['branches_sums']['dep_out']+$aValue['out_ammount_sum']>0)?($v['branches_sums']['dep_out']+$aValue['out_ammount_sum']):'&nbsp;').'</TD>

               <TD _valign=top align=center>'.($v['branches_sums']['servers']).'</TD>
               <TD _valign=top align=center>'.($v['branches_sums']['awp_plan']+$aValue['awp_count']).'</TD>
              <TD _valign=top align=center '.(($v['branches_sums']['awp_plan']+$aValue['awp_count']!=$v['branches_sums']['awp_base']+@$GLOBALS['awps'][$aValue['ID']])?'style="color: #FF0000;"':'').'>'.((@$v['branches_sums']['awp_base']+@$GLOBALS['awps'][$aValue['ID']]>0)?($v['branches_sums']['awp_base']+$GLOBALS['awps'][$aValue['ID']]):0).'&nbsp;</TD>
              <TD _valign=top align=center>'.($v['branches_sums']['payment']+$aValue['price_sum']).'</TD>
           </TR>
        ';

    }
    $sums['awp_base']+=$curOrgAWPInBaseSum;
}
$output['text'].='
               <TR>
                  <TD _valign=top colspan=10 style="font-weight: 600; text-align: right;">Всего:</TD>
                  <TD _valign=top style="font-weight: 600;" align=center>'.$sums['dep_rgl'].'</TD>
                  <TD _valign=top style="font-weight: 600;" align=center>'.$sums['dep_pln'].'</TD>
                  <TD _valign=top style="font-weight: 600;" align=center>'.$sums['dep_urg'].'</TD>
                  <TD _valign=top style="font-weight: 600;" align=center>'.$sums['dep_prj'].'</TD>
                  <TD _valign=top style="font-weight: 600;" align=center>'.$sums['dep_out'].'</TD>

                  <TD _valign=top style="font-weight: 600;" align=center>'.$sums['servers'].'</TD>
                  <TD _valign=top style="font-weight: 600;" align=center>'.$sums['awp_plan'].'</TD>
                  <TD _valign=top style="font-weight: 600;" align=center>'.$sums['awp_base'].'</TD>
                  <TD _valign=top style="font-weight: 600;" align=center>'.$sums['payment'].'</TD>

               </TR>
            ';

$output['text'].='
              </TABLE>
 ';


/////////////////// generate menu //////////////////////
$res=subscribers_menu(array('active'=>$menuActive));
$output['actions']=$res['OUTPUT'];







/*
 if ($result['RESULT']===true) {
    $output['text']=$result['OUTPUT'];
 } else {
    $output['text']=$result['ERROR'];
 }
*/

// $TWebUsers->columns[$TWebUsers->colIDs['organisation']]->refSelectcase='subscriber=0';
// $TWebUsers->columns[$TWebUsers->colIDs['organisation']]->showEmptyVariant=false;
$output['add_org']='
 ';
/*
    <FORM action="" method="POST">
       <INPUT type="hidden" name="submitted" value="1">
       Сделать абонентом: '.$TWebUsers->columns[$TWebUsers->colIDs['organisation']]->ShowEdit($TWebUsers).' <INPUT type="submit" value="Добавить">
       '.((isset($WEB_PERMISSIONS['manageClientsUsers']))?'<A HREF="/mycompany/add/" onClick="OpenNewSized(this.href,960,760); return(false);">Добавить клиента</A>':'').'
    </FORM>
*/
$output['access_control']='';
if ( (false) and ($_SESSION['user_all']['security_group']>0)) {
    $output['access_control'].='<A target=_blank HREF="/avk_admn/?T='.$GLOBALS['TWebUsersFunctions']->name.'&UID=0&FID=2&'.$GLOBALS['TRecruiting']->adminReturnPathName.'=/closewindow.html"  onClick="OpenNewSized(this.href, 960,600); return(false);" title="Доступ"><IMG src="/img/icon-recruiting-access2.gif" alt="Доступ" border=0></A>';
}


include($_SERVER['DOCUMENT_ROOT'].'/include/header.php');
?>
<TABLE cellspacing=0 cellpadding=2 border=0 width=99%>
    <TR>
        <TD><DIV class="header">Управление абонентами</DIV></TD>
        <TD align=right><?php
            echo $output['access_control'];
            ?></TD>
    </TR>
</TABLE>
<DIV class="actionsMenu" style="">
    <?php
    echo $output['actions'];
    ?>
</DIV>
<?php
echo $output['text'];
echo $output['add_org'];

// echo $GLOBALS['MAX_SESSION_TIMEOUT'];
?>
<?php
include($_SERVER['DOCUMENT_ROOT'].'/include/bottom.php');
?>
