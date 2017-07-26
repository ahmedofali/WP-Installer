<?php namespace WP ;

use \Symfony\Component\Console\Command\Command ;
use Symfony\Component\Console\Input\InputInterface ;
use Symfony\Component\Console\Output\OutputInterface ;
use Symfony\Component\Console\Input\InputArgument ;
use GuzzleHttp\ClientInterface ;
use ZipArchive ;

class Installer extends Command {

    protected $client ;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client ;

        parent::__construct() ;
    }

    public function configure()
    {
        $this->setName("new")
            ->setDescription("Create a new Wordpress application")
            ->addArgument("name" , InputArgument::REQUIRED ,"Folder Name") ;
    }

    public function execute( InputInterface $input, OutputInterface $output )
    {
        $output->writeln("<info>Starting .....</info>");

        // assert that the folder does not already exist
        $directory = getcwd()."/".$input->getArgument("name") ;

        $this->checkNoFolderExistsWithTheSameName( $directory , $output );

        // download last version of Wordpress
        $this->download( $zipFile = $this->makeFileName() )
            // extract zip file
            ->extract( $zipFile , $directory )
            // delete zip file
            ->deleteZipFile( $zipFile );


        // alert the user that they are ready to go
        $output->writeln("<info>Application ready</info>");

    }

    private function deleteZipFile($zipFile)
    {
        unlink( $zipFile );
    }

    private function makeFileName()
    {
        return getcwd()."/wp_".md5(uniqid()).".zip" ;
    }

    private function checkNoFolderExistsWithTheSameName( $directory , OutputInterface $output ){
        if( is_dir( $directory ) )
        {
            $output->writeln("<error>Application Already Exists</error>");

            exit( 1 );
        }
    }

    private function download( $zipFile )
    {
        $response = $this->client->get("https://wordpress.org/latest.zip")->getBody() ;

        file_put_contents( $zipFile , $response ) ;

        return $this ;
    }

    private function extract( $zipFile , $directory )
    {
        $archive = new ZipArchive;

        $archive->open( $zipFile ) ;

        $archive->extractTo($directory) ;

        $archive->close() ;

        return $this ;
    }

}