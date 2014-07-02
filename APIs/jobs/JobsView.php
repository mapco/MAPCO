<?php

//include cms core functions
include("../functions/cms_core.php");

//	keep post submit
$post = $_POST;
	
	/**
	 *	Sidebar Menu
	 *
	 */
	if (isset($post["view"]) && $post["view"] == "menu") {
		$html = '<div class="backend-menu">';
		$html.= '	<span><a href="javascript:view(\'jobs\');">Jobs</a></span>';
		$html.= '	<span><a href="javascript:view(\'rules\');">Ausführung/Regeln</a></span>';
		$html.= '	<span><a href="javascript:view(\'logfile\');">Logbuch</a></span';
		$html.= '</div>';
		echo $html;
	}

	/**
	 *	post view rules table
	 *
	 */
	if (isset($post["view"]) && $post["view"] == "rules") {
		$editRuleLang = 'Regel bearbeiten';
		$deleteRuleLang = 'Regel löschen';
		
		$addJobRuleLang = 'Neue Ausführungsregel eingeben';
		$addJobRule = '<img class="btn button-add" src="' . ICONS_24 . 'add.png" alt="' . $addJobRuleLang . '" title="' . $addJobRuleLang . '" onclick="AddRule()"/>';
		$html = '<table class="listing">';
			$html.= '<tr>';
			$html.= '	<th>Name der Ausführungsregel</th>';
			$html.= '	<th>zugeordnete Jobs</th>';
			$html.= '	<th class="options">' . $addJobRule . '</th>';
			$html.= '</tr>';
			
		$jobsQuery = "
			SELECT * 
			FROM jobs";
		$jobsResults = q($jobsQuery, $dbweb, __FILE__, __LINE__);
		$jobs = array();
		while ($row = mysqli_fetch_array($jobsResults))
		{
			if (!isset($jobs[$row["JobRule"]])) {
				$jobs[$row["JobRule"]] = $row["Name"];
			} else {
				$jobs[$row["JobRule"]].= ", " . $row["Name"];
			}
		}
		
		$jobRulesQuery = "
			SELECT * 
			FROM job_rules";
		$jobRulesResults = q($jobRulesQuery, $dbweb, __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($jobRulesResults)) 
		{
			$html.= '<tr>';
				$html.= '<td class="rulecol" style="width:500px">'.$row["Name"] . '</td>';
				$html.= '<td>';
				if (isset($jobs[$row["id_jobRule"]])) {
					$html.= $jobs[$row["id_jobRule"]];
				} else {
					$html.= 'Keine';
				}
				$html.= '';
				$html.= '
					<td>
						<img class="btn button-remove" src="' . ICONS_24 . 'remove.png" alt="' . $deleteRuleLang . '" title="' . $deleteRuleLang . '" onclick="deleteRule('.$row["id_jobRule"].')" />
						<img class="btn button-edit" src="' . ICONS_24 . 'edit.png" alt="' . $editRuleLang . '" title="' . $editRuleLang . '" onclick="UpdateRule('.$row["id_jobRule"].')" />
					</td>
				</tr>';
		}
		$html.= '</table>';
		echo '<div id="logfile" class="widget-listing"><h3>Ausführung / Regel</h3>' . $html . '</div>';
	}

	/**
	 *	post view jobs table
	 *
	 */
	if (isset($post["view"]) && $post["view"] == "jobs") {
		
		$addJobLang = 'Neuen Job eingeben';
		$deleteJobLang = 'Job löschen';
		$editJobLang = 'Job bearbeiten';
		$manuallyJobLang = 'Job manuell ausführen';
		
		$addJob = '<img class="btn button-add" src="' . ICONS_24 . 'add.png" alt="' . $addJobLang . '" title="' . $addJobLang. '" onclick="AddJob()" />';
		$html = '<table class="listing hover">';
			$html.= '<tr>';
				$html.= '<th>Jobs</th>';
				$html.= '<th>Nächster Aufruf</th>';
				$html.= '<th>Rule</th>';
				$html.= '<th>Status</th>';
				$html.= '<th class="options">' . $addJob . '</th>';
			$html.= '</tr>';

		$jobQuery = "
				SELECT * 
				FROM jobs 
				LEFT JOIN jobs_groups ON id_jobgroup = jobsGroupID
			";
			$jobQuery.= "
			ORDER BY Name";
		$jobsResult = q($jobQuery, $dbweb, __FILE__, __LINE__);

		//	cache job rules
		$data = array();
		$data['from'] = 'job_rules';
		$data['select'] = '*';
		$jobRules =  SQLSelect($data['from'], $data['select'], 0, 0, 0, 0, 'web',  __FILE__, __LINE__);
		if (count($jobRules) > 0)
		{
			foreach($jobRules as $jobRule)
			{
				$jobRulesList[$jobRule['id_jobRule']] = $jobRule;	
			}	
		}

		while ($row = mysqli_fetch_array($jobsResult)) 
		{
			$setGroup = "";
			if (!empty($row['jobgroup_value'])) {
				$setGroup = 'groupID' . $row['id_jobgroup'] .' group-' . $row['jobgroup_value'];	
			}
			$html.= '<tr class="set ' . $setGroup . '">';
				$html.= '<td style="width:500px"><b>' . $row["Name"] . '</b>';
				if ($row["Description"] != "") {
					$html.= '<br /><small>' . $row["Description"] . '</small>';
				}
				$html.= '</td>';
				$html.= '<td class="center date">' . getDateToday($row["NextCall"]) . '</td>';
				$html.= '<td>' . $jobRulesList[$row["JobRule"]]['Name'] . '</td>';
				if ($row["Active"] == "1") {
					$html.= '<td class="center good">aktiv</td>';
				} else {
					$html.= '<td class=" center bad">inaktiv</td>'	;
				}
				$html.= '
					<td>
						<img class="btn button-remove" src="' . ICONS_24 . 'remove.png" alt="' . $deleteJobLang . '" title="' . $deleteJobLang . '" onclick="deleteJob('.$row["id_job"].')" />
						<img class="btn button-view" src="' . ICONS_24 . 'blog_post.png" alt="Job Overview" title="Job Overview" onclick="viewJobById('.$row["id_job"].');" />
						<img class="btn button-edit" src="' . ICONS_24 . 'edit.png" alt="' . $editJobLang . '" title="' . $editJobLang . '" onclick="UpdateJob('.$row["id_job"].')" />
						<img class="btn button-play" src="' . ICONS_24 . 'play.png" alt="' . $manuallyJobLang . '" title="' . $manuallyJobLang . '" onclick="JobAusgabe('.$row["id_job"].');" />
					</td>
				</tr>';
		}
		$html.= '</table>';
		echo '<div id="logfile" class="widget-listing hover"><h3>Jobs';
		$addWhere = "jobgroup_show = 1";
		$groups = SQLSelect('jobs_groups', '*', $addWhere, 0, 0, 0, 'web',  __FILE__, __LINE__);
		foreach($groups as $group)
		{
			echo '<button class="btn h3 btn-group-' . $group['jobgroup_value'] . '" groupid="' . $group['id_jobgroup'] . '" id="jobsgroup-' . $group['id_jobgroup'] . '">' . $group['jobgroup_name'] . '</button>';	
		}
		echo '</h3>' . $html . '</div>';
	}

	/**
	 *	post view jobs logfile table
	 *
	 */
	if (isset($post["view"]) && $post["view"] == "logfile") {
		$html = '<table class="logfile">';
		$html.= '	<tr>';
		$html.= '		<th>Nr.</th>';
		$html.= '		<th>Job</th>';
		$html.= '		<th>Startzeit</th>';
		$html.= '		<th>Dauer</th>';
		$html.= '		<th>Nächster Aufruf</th>';
		$html.= '		<th>Manuell</th>';
		$html.= '		<th>Antwort</th>';
		$html.= '	</tr>';
		$i = 0;
		$jobsQuery = "
			SELECT * 
			FROM jobs_logfile 
			ORDER BY id_logfile 
			DESC LIMIT 60";
		$jobsRsults = q($jobsQuery, $dbweb, __FILE__, __LINE__);
		while( $row = mysqli_fetch_array($jobsRsults) )
		{
			$i++;
			$html.= '<tr>';
				$html.= '<td>'.$i.'</td>';
			
				//job by job ID
				$jobByIdQuery = "
					SELECT * 
					FROM jobs 
					WHERE id_job = " . $row["job_id"] . "";
				$jobByIdResult = q($jobByIdQuery, $dbweb, __FILE__, __LINE__);
				$row2 = mysqli_fetch_array($jobByIdResult);
				$html.= '<td><a href="javascript:viewJobById(' .  $row2["id_job"] . ');">' . $row2["Name"] . '</a></td>';
				$html.= '<td class="center date">' . getDateToday($row["StartTime"]) . '</td>';
				$html.= getWorktimeResult($row);
//				$results33=q("SELECT * FROM jobs WHERE id_job=".$row2["id_job"].";", $dbweb, __FILE__, __LINE__);
//				$row33=mysqli_fetch_array($results33);
//				if ($row["NextCall"] > 0) $NextCall = getDateToday($row["NextCall"]).' '.getDateToday($row33["NextCall"]);
				if ($row["NextCall"] > 0) $NextCall = getDateToday($row["NextCall"]);
					else $NextCall = "";
					$html.= '<td class="center date">' . $NextCall . '</td>';
				$html.= '<td class="center">' . getManuelStatus($row["manual"]) . '</td>';
				$html.= '<td>
						<a href="javascript: $(\'#response'.$row["id_logfile"].'\').toggle();">Anzeigen</a>
						<div style="width:600px; display:none;" id="response'.$row["id_logfile"].'">'.nl2br(htmlentities(str_replace(array("<br />", "<br>"), "\n", $row["Response"]))).'</div>
					</td>';
			$html.= '</tr>';
		}
		$html.= '</table>';
		echo '<div id="logfile" class="widget-listing"><h3>Logfile</h3>' . $html . '</div>';
	}