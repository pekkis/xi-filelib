<?php

namespace Xi\Tests\Filelib\Backend;

use \PDO;

abstract class TestCase extends \PHPUnit_Extensions_Database_TestCase {
    

    protected $connection;
    
    
    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getDataSet()
    {
        return new ArrayDataSet(array(
            'xi_filelib_folder' => array(

                array(
                    'id' => 1,
                    'parent_id' => null,
                    'folderurl' => '',
                    'foldername' => 'root',
                ),
                
                array(
                    'id' => 2,
                    'parent_id' => 1,
                    'folderurl' => 'lussuttaja',
                    'foldername' => 'lussuttaja',
                ),
                
                array(
                    'id' => 3,
                    'parent_id' => 2,
                    'folderurl' => 'lussuttaja/tussin',
                    'foldername' => 'tussin',
                ),

                array(
                    'id' => 4,
                    'parent_id' => 2,
                    'folderurl' => 'lussuttaja/banskun',
                    'foldername' => 'banskun',
                ),

                array(
                    'id' => 5,
                    'parent_id' => 2,
                    'folderurl' => 'lussuttaja/tiedoton-kansio',
                    'foldername' => 'tiedoton-kansio',
                ),

                
            ),
            
            'xi_filelib_file' => array(
            
                array(
                    'id' => 1,
                    'folder_id' => 1,
                    'mimetype' => 'image/png',
                    'fileprofile' => 'versioned',
                    'filesize' => '1000',
                    'filename' => 'tohtori-vesala.png'                    ,
                    'filelink' => 'tohtori-vesala.png',
                    'date_uploaded' => '2011-01-01 16:16:16',
                ),

                array(
                    'id' => 2,
                    'folder_id' => 2,
                    'mimetype' => 'image/png',
                    'fileprofile' => 'versioned',
                    'filesize' => '10001',
                    'filename' => 'akuankka.png'                    ,
                    'filelink' => 'lussuttaja/akuankka.png',
                    'date_uploaded' => '2011-01-01 15:15:15',
                ),

                array(
                    'id' => 3,
                    'folder_id' => 3,
                    'mimetype' => 'image/png',
                    'fileprofile' => 'default',
                    'filesize' => '10000',
                    'filename' => 'repesorsa.png'                    ,
                    'filelink' => 'lussuttaja/tussin/repesorsa.png',
                    'date_uploaded' => '2011-01-01 15:15:15',
                ),

                array(
                    'id' => 4,
                    'folder_id' => 4,
                    'mimetype' => 'image/png',
                    'fileprofile' => 'default',
                    'filesize' => '10000',
                    'filename' => 'megatussi.png'                    ,
                    'filelink' => 'lussuttaja/banskun/megatussi.png',
                    'date_uploaded' => '2011-01-02 15:15:15',
                ),

                array(
                    'id' => 5,
                    'folder_id' => 4,
                    'mimetype' => 'image/png',
                    'fileprofile' => 'default',
                    'filesize' => '10000',
                    'filename' => 'megatussi2.png'                    ,
                    'filelink' => 'lussuttaja/banskun/megatussi2.png',
                    'date_uploaded' => '2011-01-03 15:15:15',
                ),
                
                // id | folder_id | mimetype | fileprofile | filesize | filename | filelink | date_uploaded 
                
            ),
            
        ));
    }

    
    
    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        $dsn = 'pgsql:host=127.0.0.1;dbname=filelib_test';

        $pdo = new PDO($dsn, 'pekkis', 'g04753m135');
        
        $pdo->exec('PRAGMA foreign_keys = ON');

        return $this->createDefaultDBConnection($pdo);
        
    }
    
    protected function getSetUpOperation()
    {
        return \PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT(true);
    }
    
    
    protected function getTearDownOperation()
    {
        return \PHPUnit_Extensions_Database_Operation_Factory::DELETE_ALL();
    }

    
}
