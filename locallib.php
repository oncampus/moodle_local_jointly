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
	elseif ($file->component == 'mod_book') {
		$description = '';
		if (strpos($file->mimetype, 'image') !== false and $metadata = get_book_metadata_desc($file->id, $filename, $file->filearea)) {
			$description = $metadata;
		}
		if ($file->filearea == 'chapter' and $tags = get_book_chapter_metadata_tags($file->id, $file->filearea)) {
			$description .= ' (tags: '.implode(', ', $tags).')';
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

function get_book_chapter_metadata_tags($fileid, $filearea) {
	global $DB;
	$tags = array();
	
	if ($filearea == 'chapter') {
		$sql = "SELECT t.rawname, t.id 
				  FROM {tag} t, {tag_instance} ti, {files} f 
				 WHERE t.id = ti.tagid 
				   AND ti.component = 'mod_book' 
				   AND ti.itemtype = 'book_chapters' 
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

function get_book_metadata_desc($fileid, $filename, $filearea) {
	global $DB;
	if ($filearea == 'intro') {
		$sql = 'SELECT b.intro as message 
				  FROM {book} b, {course_modules} cm, {context} c, {files} f 
				 WHERE b.id = cm.instance 
				   AND cm.id = c.instanceid 
				   AND c.id = f.contextid 
				   AND f.id = :fileid ';
				   
		$params = array('fileid' => $fileid);
	}
	elseif ($filearea == 'chapter') {
		$sql = 'SELECT bc.content as message 
				  FROM {book_chapters} bc, {files} f 
				 WHERE bc.id = f.itemid 
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


function get_lom($files, $language) {
	global $CFG, $DB;
	
	if (empty($language)) {
		$language = $CFG->lang;
	}
	
	$getmetadata = get_custom_metadata($language);
		
	if (!$getmetadata) {
		redirect(new moodle_url('/local/jointly/view.php'), get_string('getmetadataerror', 'local_jointly'), null, \core\output\notification::NOTIFY_INFO);
	}		
	
	$xml = '';

    $domtree = new DOMDocument('1.0', 'UTF-8');
	
	$xmlMeta = $domtree->createElement("OAI-PMH");
	$xmlMeta->setAttribute("xmlns", "http://www.openarchives.org/OAI/2.0/");
	$xmlMeta->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
	$xmlMeta->setAttribute("xsi:schemaLocation", "http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd");

	$datetime = new DateTime(date('Y-m-d H:i:s'));
	$datetime = $datetime->format('Y-m-d\T H:i:s\Z'); // ISO8601 acc. to http://openarchives.org/OAI/openarchivesprotocol.html#Dates
	
	$xmlResponseDate = $domtree->createElement("responseDate", $datetime);
	$xmlMeta->appendChild($xmlResponseDate);
	
	$requesturi = $CFG->wwwroot . '\local\jointly\view.php?verb=GetRecord';
	$xmlRequest = $domtree->createElement("request", $requesturi);
	$xmlRequest->setAttribute("verb", "GetRecord");		
	$xmlRequest->setAttribute("metadataPrefix", $getmetadata->metadataprefix);
	$xmlRequest->setAttribute("identifier", $getmetadata->identifier);
	$xmlMeta->appendChild($xmlRequest);	
	
	$xmlGetRecord = $domtree->createElement("GetRecord");
	$xmlMeta->appendChild($xmlGetRecord);	
	
		$xmlRecord = $domtree->createElement("record");
		$xmlGetRecord->appendChild($xmlRecord);
		
			$xmlHeader = $domtree->createElement("header");
			$xmlRecord->appendChild($xmlHeader);
	
				$xmlHeaderIdentifier = $domtree->createElement("identifier", $getmetadata->identifier);
				$xmlHeader->appendChild($xmlHeaderIdentifier);
				
				$xmlHeaderDatestamp = $domtree->createElement("datestamp", $datetime);
				$xmlHeader->appendChild($xmlHeaderDatestamp);	
	
			$xmlMetaData = $domtree->createElement("metadata");
			$xmlRecord->appendChild($xmlMetaData);
	
				$xmlMetaDataLOM = $domtree->createElement("lom");
				$xmlMetaDataLOM->setAttribute("xmlns", "http://ltsc.ieee.org/xsd/LOM");
				$xmlMetaDataLOM->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
				$xmlMetaDataLOM->setAttribute("xsi:schemaLocation", "http://ltsc.ieee.org/xsd/LOM http://ltsc.ieee.org/xsd/lomv1.0/lom.xsd");	
				$xmlMetaData->appendChild($xmlMetaDataLOM);
				
				$xmlMetaGeneral = $domtree->createElement("general");
				$xmlMetaDataLOM->appendChild($xmlMetaGeneral);
				
				
				$xmlMetaIdentifier = $domtree->createElement("identifier", $getmetadata->identifier);
				$xmlMetaGeneral->appendChild($xmlMetaIdentifier);
				
				$xmlMetaTitle = $domtree->createElement("title");
				$xmlMetaGeneral->appendChild($xmlMetaTitle);
				
				$xmlMetaTitleString = $domtree->createElement("string", $getmetadata->title);
				$xmlMetaTitleString->setAttribute("language", $getmetadata->language);
				$xmlMetaTitle->appendChild($xmlMetaTitleString);

				
				$xmlMetaDescription = $domtree->createElement("description");
				$xmlMetaGeneral->appendChild($xmlMetaDescription);
				
				$xmlMetaDescriptionString = $domtree->createElement("string", $getmetadata->description);
				$xmlMetaDescriptionString->setAttribute("language", $getmetadata->language);
				$xmlMetaDescription->appendChild($xmlMetaDescriptionString);
				

				$keyword = explode(',', $getmetadata->keywords);
				foreach ($keyword as $k) {
					$xmlMetaKeyword = $domtree->createElement("keyword"); 
					$xmlMetaKeywordString = $domtree->createElement("string", $k); 
					$xmlMetaKeywordString->setAttribute("language", $getmetadata->language);
					$xmlMetaKeyword->appendChild($xmlMetaKeywordString);
					$xmlMetaGeneral->appendChild($xmlMetaKeyword);
				}	

	// Merge child elements with opening element		
	$domtree->appendChild($xmlMeta);

	// http://sodis.de/cp/oai_pmh/oai.php?verb=getRecord&identifier=BWS-04986135&metadataPrefix=oai_lom-eaf
	// https://www.oaforum.org/otherfiles/berl_oai-tutorial_de.pdf
	
	foreach ($files as $file) { 
		$item = $domtree->createElement("item");
		$xmlRecord->appendChild($item);
		
		$header = $domtree->createElement("header");
		$item->appendChild($header);
		
			$identifier = $domtree->createElement("identifier", "FHL-" . $file->id);
			$header->appendChild($identifier);
			
			$datestamp = $domtree->createElement("datestamp", date('Y-m-d H:i:s', $file->timecreated));
			$header->appendChild($datestamp);
		
		
		$filename = $file->filename;
		$desc = '';
		if ($file->component == 'mod_resource') {			
			if ($metadata = get_resource_metadata($file->id)) {
				$filename = $metadata->name;
				$desc = $metadata->intro;
			}
		}		
		
		$general = $domtree->createElement("general");
		$item->appendChild($general);
		
			$title = $domtree->createElement('title');
			$general->appendChild($title);
			
				$title_string = $domtree->createElement('string', $filename);
				$title_string->setAttribute("language", "de");
				$title->appendChild($title_string);
				
			$description = $domtree->createElement('description');
			$general->appendChild($description);
			
				$description_string = $domtree->createElement('string', $desc);
				$description_string->setAttribute("language", "de");
				$description->appendChild($description_string);
				
				
			$author = $domtree->createElement('author', $file->author);
			$general->appendChild($author);
				
			$timecreated = $domtree->createElement('timecreated', date('Y-m-d H:i:s', $file->timecreated));
			$general->appendChild($timecreated);
				
			$timemodified = $domtree->createElement('timemodified', date('Y-m-d H:i:s', $file->timemodified));
			$general->appendChild($timemodified);
							
			$licence = $domtree->createElement('licence', $file->license);
			$general->appendChild($licence);
			
			$licencedesc_get = get_licence_description_string($file->license);
			
			$licencedesc = $domtree->createElement('licencedesc', $licencedesc_get->fullname);
			$general->appendChild($licencedesc);
		
		$technical = $domtree->createElement("technical");
		$item->appendChild($technical);
				
			$format = $domtree->createElement("format", $file->mimetype);
			$technical->appendChild($format);
					
			$size = $domtree->createElement("size", $file->filesize . " KB");
			$technical->appendChild($size);
					
			$location = $domtree->createElement("location", $CFG->wwwroot.'/local/jointly/download.php?id='.$file->id);
			$technical->appendChild($location);
			
			$component = $domtree->createElement("component", $file->component);
			$technical->appendChild($component);
			
			$filearea = $domtree->createElement("filearea", $file->filearea);
			$technical->appendChild($filearea);
				
		$educational = $domtree->createElement("educational");
		$item->appendChild($educational);
		
			$learningResourceType = $domtree->createElement("learningResourceType");
			$educational->appendChild($learningResourceType);

				$source = $domtree->createElement("source");
				$learningResourceType->appendChild($source);
				
				$value = $domtree->createElement("value");
				$learningResourceType->appendChild($value);
		
	}

	if ($tempname = tempnam($CFG->dirroot.'/local/jointly/xml/', 'lom_')) {
		$xml_filename = $tempname.'.xml';
		$domtree->save($xml_filename);
		unlink($tempname);
		send_file($xml_filename, 'lom.xml', null, 0, false, false, '', true);
		unlink($xml_filename);
	}
}

function get_listidentifiers($files) {	
	global $CFG, $DB;

	$getmetadata = get_custom_metadata($CFG->lang);
		
	if (!$getmetadata) {
		redirect(new moodle_url('/local/jointly/view.php'), get_string('getmetadataerror', 'local_jointly'), null, \core\output\notification::NOTIFY_INFO);
	}	
	
	$xml = '';

    $domtree = new DOMDocument('1.0', 'UTF-8');
	
	$xmlMeta = $domtree->createElement("OAI-PMH");
	$xmlMeta->setAttribute("xmlns", "http://www.openarchives.org/OAI/2.0/");
	$xmlMeta->setAttribute("xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
	$xmlMeta->setAttribute("xsi:schemaLocation", "http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd");

	$datetime = new DateTime(date('Y-m-d H:i:s'));
	$datetime = $datetime->format('Y-m-d\T H:i:s\Z'); // ISO8601 acc. to http://openarchives.org/OAI/openarchivesprotocol.html#Dates	
	$xmlResponseDate = $domtree->createElement("responseDate", $datetime);
	$xmlMeta->appendChild($xmlResponseDate);
	
	
	$requesturi = $CFG->wwwroot . '\local\jointly\view.php?verb=ListIdentifiers';
	$xmlRequest = $domtree->createElement("request", $requesturi);
	$xmlRequest->setAttribute("verb", "ListIdentifiers");
	$xmlRequest->setAttribute("metadataPrefix", $getmetadata->metadataprefix);
	$xmlMeta->appendChild($xmlRequest);	
	
	$xmlListIdentifiers = $domtree->createElement("ListIdentifiers");
	$xmlMeta->appendChild($xmlListIdentifiers);	
	
	
	// Merge child elements with opening element		
	$domtree->appendChild($xmlMeta);


	// http://sodis.de/cp/oai_pmh/oai.php?verb=getRecord&identifier=BWS-04986135&metadataPrefix=oai_lom-eaf
	// https://www.oaforum.org/otherfiles/berl_oai-tutorial_de.pdf
	
	foreach ($files as $file) { 
		
		$header = $domtree->createElement("header");
		$xmlListIdentifiers->appendChild($header);
		
			$identifier = $domtree->createElement("identifier", $getmetadata->listidentprefix . $file->id);
			$header->appendChild($identifier);
			
			$datestamp = new DateTime(date('Y-m-d H:i:s'));
			$datestamp = $datestamp->format('Y-m-d\T H:i:s\Z'); // ISO8601 acc. to http://openarchives.org/OAI/openarchivesprotocol.html#Dates	
			$xmlDatestamp = $domtree->createElement("datestamp", $datestamp);
			$header->appendChild($xmlDatestamp);		
		
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

function get_licence_description_string($licence) {
	global $DB;
	
	$licence_desc = $DB->get_record_sql('SELECT fullname FROM {license} WHERE shortname = ?', array($licence));
	
	return $licence_desc;
}

function get_custom_metadata($language) {
	global $DB;
	
	$custom_metadata = $DB->get_record('local_jointly', array('language' => $language));
	
	if (!empty($custom_metadata)) {
		return $custom_metadata;
	} else {
		return false;
	}
	
}

function meta_language_switch() {
	global $DB;
	
	$sql = "SELECT language FROM {local_jointly}";
	
	$language = $DB->get_records_sql($sql);
	
	foreach($language as $la) {
		$langurl = new moodle_url('/local/jointly/meta_edit.php?language=' . $la->language);		
		echo html_writer::link($langurl, $la->language) . " - ";
	}
	
	$newlang = new moodle_url('/local/jointly/meta_edit.php');
	echo html_writer::link($newlang, get_string('editmeta_newlanguage', 'local_jointly'));
	echo "<hr>";

}