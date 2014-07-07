<?php
/***
 * @author: rlange@mapco.de
 * CORE API - Images
 *
 *******************************************************************************/

DEFINE('IMAGE_LOCATION_PATH',        	'http://www.mapco.de/files/');
DEFINE('IMAGE_NO_PATH',					'http://www.mapco.de/files_thumbnail/0.jpg');
DEFINE('IMAGE_FORMAT_ID',            	19);
DEFINE('IMAGE_FORMAT_THUMB_ID',      	8);

/**
 * Returns an image by article id
 *
 * @param $item
 * @param array $criteria
 * @return array
 *
 * @criteria
 *	- htmlImgTag - returns a html img tag <img src="">
 */
function getImagesByArticleId($item, $criteria = array())
{
    //	get Image Location
    if ($item["article_id"] != null)
    {
        $images = array();
        $data = array();
        $data['from'] = 'cms_articles_images';
        $data['select'] = '*';
        $data['where'] = "
            article_id = '" . $item["article_id"] . "'
        ";
        $articleImagesResults = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'web',  __FILE__, __LINE__);
        if (count($articleImagesResults) > 0)
        {
            foreach($articleImagesResults as $articleImages)
            {
                $data['from'] = 'cms_files';
                $data['select'] = '*';
                $data['where'] = "
                    original_id = '" . $articleImages["file_id"] . "'
                    AND imageformat_id = '" . IMAGE_FORMAT_ID . "'
                ";
                $originalFiles = SQLSelect($data['from'], $data['select'], $data['where'], 0, 0, 0, 'web',  __FILE__, __LINE__);
                if (count($originalFiles) > 0)
                {
                    $saveImages = null;
                    foreach($originalFiles as $originalFile)
                    {
                        $saveImages.= IMAGE_LOCATION_PATH . floor(bcdiv($originalFile["id_file"], 1000)) . '/' . $originalFile["id_file"] . '.' . $originalFile["extension"] . "\n";
                    }

                    //	create a Thumbnail
                    $data = array();
                    $data["API"] = "cms";
                    $data["APIRequest"] = "ImageThumbnail";
                    $data['APICleanRequest'] = true;
                    $data["id_file"] = $originalFile['id_file'];
                    $images['imageLocationThumb'] = post(PATH."soa2/", $data);

                } else {
                    //	clear old images in imageLocation and imageLocationThumb
                    $saveImages = "";
                    $images['imageLocationThumb'] = "";
                }
            }

            if (isset($criteria['htmlImgTag']) && $criteria['htmlImgTag'] == true)
            {
                if (!empty($images['imageLocationThumb'])) {
                    return '-<img src="' . $images['imageLocationThumb'] . '">';
                } else {
                    return '<img src="' . IMAGE_NO_PATH . '">';
                }
            }

            return $images;
        }
        return '<img src="' . IMAGE_NO_PATH . '">';
    }
}
