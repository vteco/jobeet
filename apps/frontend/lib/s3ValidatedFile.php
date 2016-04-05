<?php

require_once sfConfig::get('sf_lib_dir') . '/vendor/autoload.php';

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class s3ValidatedFile extends sfValidatedFile
{
    public function save($file = null, $fileMode = 0666, $create = true, $dirMode = 0777)
    {
        if (null === $file)
        {
          $file = $this->generateFilename();
        }

        if ($file[0] != '/' && $file[0] != '\\' && !(strlen($file) > 3 && ctype_alpha($file[0]) && $file[1] == ':' && ($file[2] == '\\' || $file[2] == '/')))
        {
          if (null === $this->path)
          {
            throw new RuntimeException('You must give a "path" when you give a relative file name.');
          }

          $file = $this->path.DIRECTORY_SEPARATOR.$file;
        }

        // get our directory path from the destination filename
        $directory = dirname($file);

        if (!is_readable($directory))
        {
          if ($create && !@mkdir($directory, $dirMode, true))
          {
            // failed to create the directory
            throw new Exception(sprintf('Failed to create file upload directory "%s".', $directory));
          }

          // chmod the directory since it doesn't seem to work on recursive paths
          chmod($directory, $dirMode);
        }

        if (!is_dir($directory))
        {
          // the directory path exists but it's not a directory
          throw new Exception(sprintf('File upload path "%s" exists, but is not a directory.', $directory));
        }

        if (!is_writable($directory))
        {
          // the directory isn't writable
          throw new Exception(sprintf('File upload path "%s" is not writable.', $directory));
        }

        // copy the temp file to the destination file
        copy($this->getTempName(), $file);

        // chmod our file
        chmod($file, $fileMode);
        
        $this->sendFileToServer($file);
        
        return null === $this->path ? $file : str_replace($this->path.DIRECTORY_SEPARATOR, '', $file);
    }
    
    public function sendFileToServer($file)
    {
        // FETCH & CONFIGURE S3Client
        $s3Client = new Aws\S3\S3Client(array(
            'version'       =>  'latest',
            'region'        =>  'eu-west-1',
            'credentials'   =>  array(
                'key'           =>  'AKIAJ7FKSH4N3TRUMIDQ',
                'secret'        =>  '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'
            ),
            'scheme'        =>  'http'
        ));
        
        // SEND & FETCH Object to CDN
        $result = $s3Client->putObject([
            'Bucket'        =>  'upload.primesenergie.fr',
            'Key'           =>  $this->parseFilename($file),
            // 'Body'          =>  'Just a test',
            'SourceFile'    =>  $file,
            'ContentType'   =>  'text/plain',
            // 'ACL'           =>  'public-read'
        ]);
        
        var_dump($result);die;
    }
    
    // PARSE AND FETCH PATH FROM UPLOAD DIR
    public function parseFilename($file)
    {
        $result = 'uploads';
        
        $uploadDir = explode(DIRECTORY_SEPARATOR, sfConfig::get('sf_upload_dir'));
        $explodedFile = explode(DIRECTORY_SEPARATOR, $file);
        
        array_pop($explodedFile);
        
        foreach ($explodedFile as $k => $dir) {
            if (isset($uploadDir[$k])) {
                continue;
            }
            
            $result .= '/' . $dir;
        }
        
        return $result . '/' . $this->getOriginalName();
    }
}