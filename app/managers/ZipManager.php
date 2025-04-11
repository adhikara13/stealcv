<?php

class ZipManager
{
    private $zip;

    // ---------------------------------------------------------------------------------------
    // __construct
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function __construct()
    {
    }

    // ---------------------------------------------------------------------------------------
    // __destruct
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function __destruct()
    {
        $this->zip->close();
    }
    
    // ---------------------------------------------------------------------------------------
    // CreateZip
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function CreateZip($zip_name)
    {
        $zip_path = LOGS_PATH."/". basename($zip_name);
	
        $this->zip = new ZipArchive;
        
        if ($this->zip->open($zip_path, ZipArchive::CREATE) === TRUE)
        {
            return true;
        }
        
        return false;
    }

    // ---------------------------------------------------------------------------------------
    // OpenZip
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function OpenZip($zip_name)
    {
        $zip_path = LOGS_PATH."/". basename($zip_name);
	
        $this->zip = new ZipArchive;
        
        if ($this->zip->open($zip_path) === TRUE) 
        {
            return true;
        }
        
        return false;
    }

    // ---------------------------------------------------------------------------------------
    // AddFile
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function AddFile($file_name, $file, $rewriting)
    {
        if($rewriting)
        {
            $this->zip->addFromString($file_name, $file);
        }
        else
        {
            if ($this->zip->getFromName($file_name) != false)
            {
                $_file = $this->zip->getFromName($file_name);
                
                $_file .= $file;
                
                $this->zip->addFromString($file_name, $_file);
            }
            else
            {
                $this->zip->addFromString($file_name, $file);
            }
        }
    }

    // ---------------------------------------------------------------------------------------
    // DeleteFile
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function DeleteFile($file_name)
    {
        $this->zip->deleteName($file_name);
    }

    // ---------------------------------------------------------------------------------------
    // AddComment
    //
    // 
    // ---------------------------------------------------------------------------------------
    public function AddComment($comment)
    {
        $this->zip->setArchiveComment($comment);
    }
}

?>