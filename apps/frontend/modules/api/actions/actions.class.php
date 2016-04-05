<?php

/**
 * api actions.
 *
 * @package    jobeet
 * @subpackage api
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class apiActions extends sfActions
{
    public function executeSend(sfWebRequest $request)
    {
        // FETCH FILES FROM REQUEST
        $files = $request->getFiles();
        
        foreach($files as $file)
        {
            
        }
    }
}
