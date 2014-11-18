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
 * This plugin is used to upload files
 *
 * @since 2.0
 * @package    repository_upload
 * @copyright  2010 Dongsheng Cai {@link http://dongsheng.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/repository/lib.php');

/**
 * A repository plugin to allow user uploading files
 *
 * @since 2.0
 * @package    repository_upload
 * @copyright  2009 Dongsheng Cai {@link http://dongsheng.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class repository_upload_face extends repository {
    private $mimetypes = array();

    /**
     * Print a upload form
     * @return array
     */
    public function print_login() {
        return $this->get_listing();
    }

    /**
     * Process uploaded file
     * @return array|bool
     */
    public function upload($saveas_filename, $maxbytes) {
        global $CFG;

        $types = optional_param('accepted_types', '*', PARAM_RAW);
        $savepath = optional_param('savepath', '/', PARAM_PATH);
        $itemid   = optional_param('itemid', 0, PARAM_INT);
        $license  = optional_param('license', $CFG->sitedefaultlicense, PARAM_TEXT);
        $author   = optional_param('author', '', PARAM_TEXT);
        $areamaxbytes = optional_param('areamaxbytes', 'FILE_AREA_MAX_BYTES_UNLIMITED', PARAM_INT);
        $overwriteexisting = optional_param('overwrite', false, PARAM_BOOL);

        return $this->process_upload_face($saveas_filename, $maxbytes, $types, $savepath, $itemid, $license, $author, $overwriteexisting, $areamaxbytes);
    }

    /**
     * Do the actual processing of the uploaded file
     * @param string $saveas_filename name to give to the file
     * @param int $maxbytes maximum file size
     * @param mixed $types optional array of file extensions that are allowed or '*' for all
     * @param string $savepath optional path to save the file to
     * @param int $itemid optional the ID for this item within the file area
     * @param string $license optional the license to use for this file
     * @param string $author optional the name of the author of this file
     * @param bool $overwriteexisting optional user has asked to overwrite the existing file
     * @param int $areamaxbytes maximum size of the file area.
     * @return object containing details of the file uploaded
     */

	public function get_user_picture() {
		
		global $USER,$CFG, $DB, $OUTPUT;
  		$context = get_context_instance(CONTEXT_USER, $USER->id);
        $fs = get_file_storage();
 	
		// Prepare file record object
		$fileinfo = array(
		    'component' => 'user',     // usually = table name
		    'filearea' => 'icon',     // usually = table name
		    'itemid' => 0,               // usually = ID of row in table
		    'contextid' => $context->id, // ID of context
		    'filepath' => '/',           // any path beginning and ending in /
		    'filename' => 'f1/.png'); // any filename
		 
		// Get file
		$file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
		                      $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
		 
		// Read contents
		if ($file) {
		    $src = $file->get_content();
		} else {
		    $src = "Adasdasd";
		}
		
		
		return $src;
	}

    public function process_upload_face($saveas_filename, $maxbytes, $types = '*', $savepath = '/', $itemid = 0,
                                        $license = null, $author = '', $overwriteexisting = false, $areamaxbytes = FILE_AREA_MAX_BYTES_UNLIMITED) {
        global $USER, $CFG;
      

        if ((is_array($types) and in_array('*', $types)) or $types == '*') {
            $this->mimetypes = '*';
        } else {
            foreach ($types as $type) {
                $this->mimetypes[] = mimeinfo('type', $type);
            }
        }

        if ($license == null) {
            $license = $CFG->sitedefaultlicense;
        }

        $record = new stdClass();
        $record->filearea = 'draft';
        $record->component = 'user';
        $record->filepath = $savepath;
        $record->itemid   = $itemid;
        $record->license  = $license;
        $record->author   = $author;
        $record->filename = $saveas_filename.'.jpg';

        $context = get_context_instance(CONTEXT_USER, $USER->id);

        $fs = get_file_storage();

        if ($record->filepath !== '/') {
            $record->filepath = file_correct_filepath($record->filepath);
        }


        $record->contextid = $context->id;
        $record->userid    = $USER->id;

        $id = $USER->id; //New line
        $url = "/var/moodledata20/$saveas_filename.jpg"; // New line
        $stored_file = $fs->create_file_from_pathname($record, $url);
        unlink($url);

        return array(
            'url'=>moodle_url::make_draftfile_url($record->itemid, $record->filepath, $record->filename)->out(false),
            'id'=>$record->itemid,
            'file'=>$record->filename);
    }


    /**
     * Checks the contents of the given file is not completely NULL - this can happen if a
     * user drags & drops a folder onto a filemanager / filepicker element
     * @param string $filepath full path (including filename) to file to check
     * @return true if file has at least one non-null byte within it
     */
    protected function check_valid_contents($filepath) {
        $buffersize = 4096;

        $fp = fopen($filepath, 'r');
        if (!$fp) {
            return false; // Cannot read the file - something has gone wrong
        }
        while (!feof($fp)) {
            // Read the file 4k at a time
            $data = fread($fp, $buffersize);
            if (preg_match('/[^\0]+/', $data)) {
                fclose($fp);
                return true; // Return as soon as a non-null byte is found
            }
        }
        // Entire file is NULL
        fclose($fp);
        return false;
    }

    /**
     * Return a upload form
     * @return array
     */
    public function get_listing($path = '', $page = '') {
        global $CFG;
        $ret = array();
        $ret['nologin']  = true;
        $ret['nosearch'] = true;
        $ret['norefresh'] = true;
        $ret['list'] = array();
        $ret['dynload'] = false;
        $ret['upload'] = array('label'=>get_string('attachment', 'repository'), 'id'=>'repo-form-face');
        $ret['allowcaching'] = true; // indicates that result of get_listing() can be cached in filepicker.js
        return $ret;
    }

    /**
     * supported return types
     * @return int
     */
    public function supported_returntypes() {
        return FILE_INTERNAL;
    }
}
