<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    local_jointly
 * @copyright  2017 Jan Rieger, ILD, University of Applied Sciences Lübeck <jan.rieger@fh-luebeck.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function download_file($fileid) {
	global $DB, $CFG;
	
	if ($file = $DB->get_record('files', array('id' => $fileid))) {
		
		$file_storage = get_file_storage();

		$stored_file = $file_storage->get_file($file->contextid,
											  $file->component,
											  $file->filearea,
											  $file->itemid,
											  $file->filepath,
											  $file->filename);
								  
		send_stored_file($stored_file, null, 0, false);
	}
	
}

function get_table_row($file) {
	global $CFG, $DB;
	$data = array();

	$filename = $file->filename;
	$description = '';
	
	// Weitere Metadaten laden
	if ($file->component == 'mod_resource') {
		if ($metadata = get_resource_metadata($file->id)) {
			$filename = $metadata->name;
			$description = $metadata->intro;
		}
	}
	elseif ($file->component == 'mod_label') {
		$description = '';
		if ($metadata = get_label_metadata_desc($file->id, $filename, $file->mimetype)) {
			$description = $metadata;
		}
	}
	elseif ($file->component == 'mod_page') {
		if ($metadata = get_page_metadata_desc($file->id, $filename, $file->mimetype)) {
			$description = $metadata;
		}
	}
	elseif ($file->component == 'mod_forum' and ($file->filearea == 'post' or $file->filearea == 'intro')) {
		$description = '';
		if ($metadata = get_forum_metadata_desc($file->id, $filename, $file->mimetype, $file->filearea)) {
			$description = $metadata;
			if ($tags = get_forum_post_metadata_tags($file->id, $file->filearea)) {
				$description .= ' (tags: '.implode(', ', $tags).')';
			}
		}
	}
	elseif ($file->component == 'mod_folder') {
		/* if ($tags = get_context_tags($file->id)) {
			$description = ' tags: '.implode(', ', $tags);
		} */
	}
	elseif ($file->component == 'mod_wiki') {
		$description = '';
		if ($metadata = get_wiki_metadata_desc($file->id, $filename, $file->mimetype, $file->filearea)) {
			$description = $metadata;
		}
		if ($tags = get_wiki_pages_metadata_tags($file->id, $file->filearea)) {
			$description .= ' (tags: '.implode(', ', $tags).')';
		}
	}
	elseif ($file->component == 'mod_lesson') {
		$description = '';
		if (strpos($file->mimetype, 'image') !== false and $metadata = get_lesson_metadata_desc($file->id, $filename, $file->filearea)) {
			$description = $metadata;
		}
	}
	elseif ($file->component == 'mod_data') {
		$description = '';
		if ($metadata = get_data_metadata_desc($file->id, $filename, $file->filearea)) {
			$description = $metadata;
		}
	}
	
	if ($tags = get_context_tags($file->id)) {
		$description .= ' (tags: '.implode(', ', $tags).')';
	}
	
	$url = $CFG->wwwroot.'/local/jointly/download.php?id='.$file->id;
	$filename = '<a href="'.$url.'" target="_blank" title="'.$filename.'">'.$filename.'</a>';
	
	$data[] = $filename;
	$data[] = $description;
	
	$license = $file->license;
	
	$sql = 'SELECT shortname, fullname, source 
			  FROM {license} 
			 WHERE source != :source ';
			 
	$params = array('source' => '');

	$licenses = $DB->get_records_sql($sql, $params);
	if (isset($licenses[$file->license])) {
		$license = '<a href="'.$licenses[$file->license]->source.'" target="_blank">'.$file->license.'</a>';
	}
	
	$data[] = $license;
	$data[] = $file->filesize;
	$data[] = $file->mimetype;
	$data[] = $file->author;
	$data[] = $file->component;
	$data[] = $file->filearea;
	$data[] = $file->timecreated;
	$data[] = $file->timemodified;
	
	return $data;
}

function get_wiki_pages_metadata_tags($fileid, $filearea) {
	global $DB;
	$tags = array();
	
	if ($filearea == 'attachments') {
		$sql = "SELECT t.rawname, t.id 
				  FROM {tag} t, {tag_instance} ti, {files} f 
				 WHERE t.id = ti.tagid 
				   AND ti.component = 'mod_wiki' 
				   AND ti.itemtype = 'wiki_pages' 
				   AND ti.itemid = f.itemid 
				   AND f.id = :fileid ";
				   
		$params = array('fileid' => $fileid);
		if ($metadata = $DB->get_records_sql($sql, $params)) {
			foreach ($metadata as $tag) {
				$tags[] = $tag->rawname;
			}	
		}
	}
	
	if (count($tags) > 0) {
		return $tags;
	}
	
	return false;
}

function get_forum_post_metadata_tags($fileid, $filearea) {
	global $DB;
	$tags = array();
	
	if ($filearea == 'post') {
		$sql = "SELECT t.rawname, t.id 
				  FROM {tag} t, {tag_instance} ti, {files} f 
				 WHERE t.id = ti.tagid 
				   AND ti.component = 'mod_forum' 
				   AND ti.itemtype = 'forum_posts' 
				   AND ti.itemid = f.itemid 
				   AND f.id = :fileid ";
				   
		$params = array('fileid' => $fileid);
		if ($metadata = $DB->get_records_sql($sql, $params)) {
			foreach ($metadata as $tag) {
				$tags[] = $tag->rawname;
			}	
		}
	}
	
	if (count($tags) > 0) {
		return $tags;
	}
	
	return false;
}

function get_data_metadata_desc($fileid, $filename, $filearea) {
	global $DB;
	
	if ($filearea == 'intro') {
		$sql = 'SELECT d.intro as message 
				  FROM {data} d, {course_modules} cm, {context} c, {files} f 
				 WHERE d.id = cm.instance 
				   AND cm.id = c.instanceid 
				   AND c.id = f.contextid 
				   AND f.id = :fileid ';
				   
		$params = array('fileid' => $fileid);
	}
	elseif ($filearea == 'content') {
		$sql = 'SELECT df.type, df.description, dc.content, dc.content1 
				  FROM {data_fields} df, {data_content} dc, {files} f 
				 WHERE df.id = dc.fieldid 
				   AND dc.id = f.itemid 
				   AND f.id = :fileid ';
				   
		$params = array('fileid' => $fileid);
		if ($metadata = $DB->get_record_sql($sql, $params)) {
			if ($metadata->type == 'picture') {
				return $metadata->content1;
			}
			elseif ($metadata->type == 'file') {
				return $metadata->description;
			}
			elseif ($metadata->type == 'textarea') {
				$description = get_image_description($metadata->content, $filename);
				if ($description != '') {
					return $description;
				}
			}
		}
	}
	
	if (isset($sql) and isset($params) and $metadata = $DB->get_record_sql($sql, $params)) {	
		$description = get_image_description($metadata->message, $filename);
		if ($description != '') {
			return $description;
		}
	}
	
	return false;
}

function get_lesson_metadata_desc($fileid, $filename, $filearea) {
	global $DB;
	
	if ($filearea == 'intro') {
		$sql = 'SELECT l.intro as message 
				  FROM {lesson} l, {course_modules} cm, {context} c, {files} f 
				 WHERE l.id = cm.instance 
				   AND cm.id = c.instanceid 
				   AND c.id = f.contextid 
				   AND f.id = :fileid ';
				   
		$params = array('fileid' => $fileid);
	}
	elseif ($filearea == 'page_contents') {
		$sql = 'SELECT lp.contents as message 
				  FROM {lesson_pages} lp, {files} f 
				 WHERE lp.id = f.itemid 
				   AND f.id = :fileid ';
				   
		$params = array('fileid' => $fileid);
	}
	
	if (isset($sql) and isset($params) and $metadata = $DB->get_record_sql($sql, $params)) {	
		$description = get_image_description($metadata->message, $filename);
		if ($description != '') {
			return $description;
		}
	}
	
	return false;
}

function get_wiki_metadata_desc($fileid, $filename, $mimetype, $filearea) {
	global $DB;
	$sql = '';
	$params = array();
	
	if ($filearea == 'intro') {
		$sql = 'SELECT w.intro as message 
				  FROM {wiki} w, {course_modules} cm, {context} c, {files} f 
				 WHERE w.id = cm.instance 
				   AND cm.id = c.instanceid 
				   AND c.id = f.contextid 
				   AND f.id = :fileid ';
				   
		$params = array('fileid' => $fileid);
	
	}
	else {
		$sql = 'SELECT wp.cachedcontent as message 
				  FROM {wiki_pages} wp, {files} f 
				 WHERE wp.id = f.itemid 
				   AND f.id = :fileid ';
				   
		$params = array('fileid' => $fileid);
	}
	if ($metadata = $DB->get_record_sql($sql, $params)) {
		
		if (strpos($mimetype, 'image') !== false) {
			$description = get_image_description($metadata->message, $filename);
			if ($description != '') {
				return $description;
			}
		}
	}
	return false;
}

function get_forum_metadata_desc($fileid, $filename, $mimetype, $filearea) {
	global $DB;
	
	if ($filearea == 'intro') {
		$sql = 'SELECT forum.intro as message, forum.id 
				  FROM {forum} forum, {course_modules} cm, {context} c, {files} f 
				 WHERE forum.id = cm.instance 
				   AND cm.id = c.instanceid 
				   AND c.id = f.contextid 
				   AND f.id = :fileid ';
				 
		$params = array('fileid' => $fileid);
	}
	else {
		$sql = 'SELECT fp.message, fp.id 
				  FROM {forum_posts} fp, {files} f 
				 WHERE fp.id = f.itemid 
				   AND f.id = :fileid ';
				 
		$params = array('fileid' => $fileid);
	}
		
	if ($metadata = $DB->get_record_sql($sql, $params)) {
		if (strpos($mimetype, 'image') !== false) {
			$description = get_image_description($metadata->message, $filename);
			if ($description != '') {
				return $description;
			}
		}
	}
	return false;
}

function get_page_metadata_desc($fileid, $filename, $mimetype) {
	global $DB;
	$sql = 'SELECT p.name, p.content 
			  FROM {page} p, {course_modules} cm, {context} c, {files} f 
			 WHERE p.id = cm.instance 
			   AND cm.id = c.instanceid 
			   AND c.id = f.contextid 
			   AND f.id = :fileid ';
			 
	$params = array('fileid' => $fileid);

	if ($metadata = $DB->get_record_sql($sql, $params)) {
		if (strpos($mimetype, 'image') !== false) {
			$description = get_image_description($metadata->content, $filename);
			if ($description != '') {
				return $description;
			}
		}
	}
	return false;
}

function get_label_metadata_desc($fileid, $filename, $mimetype) {
	global $DB;
	$sql = 'SELECT l.name, l.intro 
			  FROM {label} l, {course_modules} cm, {context} c, {files} f 
			 WHERE l.id = cm.instance 
			   AND cm.id = c.instanceid 
			   AND c.id = f.contextid 
			   AND f.id = :fileid ';
			 
	$params = array('fileid' => $fileid);

	if ($metadata = $DB->get_record_sql($sql, $params)) {
		if (strpos($mimetype, 'image') !== false) {
			$description = get_image_description($metadata->intro, $filename);
			if ($description != '') {
				return $description;
			}
		}
	}
	return false;
}

function get_resource_metadata($fileid) {
	global $DB;
	$sql = 'SELECT r.name, r.intro 
			  FROM {resource} r, {course_modules} cm, {context} c, {files} f 
			 WHERE r.id = cm.instance 
			   AND cm.id = c.instanceid 
			   AND c.id = f.contextid 
			   AND f.id = :fileid ';
			 
	$params = array('fileid' => $fileid);

	if ($metadata = $DB->get_record_sql($sql, $params)) {		
		return $metadata;
	}
	return false;
}

function get_context_tags($fileid) {
	global $DB;
	$sql = "SELECT t.rawname 
			  FROM {tag} t, {tag_instance} ti, {files} f 
			 WHERE t.id = ti.tagid 
			   AND ti.itemtype = 'course_modules'
			   AND ti.contextid = f.contextid 
			   AND f.id = :fileid ";
			  
	$params = array('fileid' => $fileid);
	
	if ($result = $DB->get_records_sql($sql, $params)) {
		$tags = array();
		foreach ($result as $tag) {
			$tags[] = $tag->rawname;
		}
		return $tags;
	}
	return false;
}

function get_image_description($html, $filename) {
	$offset = 0;
	$images = array();
	while ($offset !== false) {
		$offset = strpos($html, '<img', $offset);
		if ($offset !== false) {
			$end = strpos($html, '>', $offset);
			if ($end !== false) {
				$images[] = substr($html, $offset, $end - $offset + 1);
				$offset++;
			}
		}
	}
	foreach ($images as $image) {
		if (strpos($image, rawurlencode($filename)) !== false) {
			$offset = strpos($image, 'alt="');
			if ($offset !== false) {
				$end = strpos($image, '"', $offset + 5);
				if ($end !== false) {
					$result = substr($image, $offset + 5, $end - $offset - 5);
					return $result;
				}
			}
		}
	}
	return '';
}

function get_metadata_array($files) {
	global $CFG, $DB;
	$metadata = array();
	foreach ($files as $file) {
		$data = new stdClass();
		
		$filename = $file->filename;
		$desc = '';
		if ($file->component == 'mod_resource') {			
			if ($meta = get_resource_metadata($file->id)) {
				$filename = $meta->name;
				$desc = $meta->intro;
			}
		}
		
		$data->filename = $filename;
		$data->description = $desc;
		$data->url = strtolower(moodle_url::make_pluginfile_url($file->contextid, 
														 $file->component, 
														 $file->filearea, 
														 $file->itemid, 
														 $file->filepath, 
														 $file->filename));
		$data->license = $file->license;
		if (isset($licenses[$file->license])) {
			$data->license_source = $licenses[$file->license]->source;
		}
		$data->filesize = $file->filesize;
		$data->mimetype = $file->mimetype;
		$data->author = $file->author;
		$data->component = $file->component;
		$data->filearea = $file->filearea;
		$data->timecreated = $file->timecreated;
		$data->timemodified = $file->timemodified;
		
		
		
		$metadata[] = $data;
	}
	return $metadata;
}

function get_lom($files) {
	global $CFG, $DB;

	$xml = '';

    $domtree = new DOMDocument('1.0', 'UTF-8');

    $xmlRoot = $domtree->createElement("lom");

    $xmlRoot = $domtree->appendChild($xmlRoot);

	foreach ($files as $file) {
		$currentfile = $domtree->createElement("general");
		$currentfile = $xmlRoot->appendChild($currentfile);

		$title = $domtree->createElement('title');
		$currentfile->appendChild($title);
		
		$description = $domtree->createElement('description');
		$currentfile->appendChild($description);
		
		$filename = $file->filename;
		$desc = '';
		if ($file->component == 'mod_resource') {			
			if ($metadata = get_resource_metadata($file->id)) {
				$filename = $metadata->name;
				$desc = $metadata->intro;
			}
		}
		
		$title->appendChild($domtree->createElement('string', $filename));
		$description->appendChild($domtree->createElement('string', $desc));
	}

	if ($tempname = tempnam($CFG->dirroot.'/local/jointly/xml/', 'lom_')) {
		$xml_filename = $tempname.'.xml';
		$domtree->save($xml_filename);
		unlink($tempname);
		send_file($xml_filename, 'lom.xml', null, 0, false, false, '', true);
		unlink($xml_filename);
	}
}

function get_license_types_string($license_ids) {
	global $DB;
	
	$licenses = $DB->get_records('license');
	
	$ids = '';
	if (count($license_ids) > 0) {
		$ids = '(';
		foreach ($license_ids as $id) {
			if (strpos($ids, "'") !== false) {
				$ids .= ', ';
			}
			$ids .= "'".$licenses[$id]->shortname."'";
		}
		$ids .= ')';
	}
	
	return $ids;
}