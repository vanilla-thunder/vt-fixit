<?php
include( dirname( __FILE__ )."/core/oxstr.php" );

class superhero
{
    function __construct()
    {
        include( dirname( __FILE__ )."/config.inc.php" );
        include( dirname( __FILE__ )."/core/oxconfk.php" );
    }

    private function getDb()
    {
        return new PDO( 'mysql:host='.$this->dbHost.';dbname='.$this->dbName, $this->dbUser, $this->dbPwd );
    }

    function getAllModuleEntries()
    {
        $ironman = $this->getDb();
        $ret = $ironman->query( 'SELECT oxvarname, oxvartype FROM oxconfig WHERE oxvarname LIKE  "%module%"', PDO::FETCH_ASSOC ) or die ( "crap!" );
        //var_dump($ret);
        $ironman = null;
        return $ret;
    }

    function arr2multiline( $aInput )
    {
        if (is_array( $aInput ))
        {
            $sMultiline = '';
            foreach ($aInput as $sKey => $sVal)
            {
                if ($sMultiline)
                {
                    $sMultiline .= "\n";
                }
                $sMultiline .= $sKey." => ".$sVal;
            }
            return $sMultiline;
        }
    }

    function multiline2arr( $sMultiline )
    {
        $aArr = array();
        $aLines = explode( "\n", $sMultiline );
        foreach ($aLines as $sLine)
        {
            $sLine = trim( $sLine );
            if ($sLine!="" && preg_match( "/(.+)=>(.+)/", $sLine, $aRegs ))
            {
                $sKey = trim( $aRegs[1] );
                $sVal = trim( $aRegs[2] );
                if ($sKey!="" && $sVal!="")
                {
                    $aArr[$sKey] = $sVal;
                }
            }
        }
        return $aArr;
    }

    function get( $sOxVarName )
    {
        $ironman = $this->getDb();
        $ret = $ironman->query( 'SELECT DECODE( oxvarvalue, "'.$this->sConfigKey.'") AS oxvarvalue FROM oxconfig WHERE oxvarname = "'.$sOxVarName.'"', PDO::FETCH_ASSOC ) or die ( "crap!" );
        $ironman = null;
        if(!$ret->rowCount()) die("ERROR: " . $sOxVarName . " has an empty value!");

        return $ret->fetch()["oxvarvalue"];
    }

    function update( $sVarName, $mVarValue )
    {
        try
        {
            $ironman = $this->getDb();

            switch ($sVarName)
            {
                case "sUtilModule":


                    break;
                case "aModuleVersions":


                    break;
                case "aModulePaths":


                    break;
                case "aModuleEvents":


                    break;
                case "aDisabledModules":


                    break;
                case "aModuleFiles":


                    break;
                case "aModuleTemplates":


                    break;
                case "aModules":
                    $mystique = $ironman->quote(serialize($this->multiline2arr( $mVarValue )));
                    $query = 'UPDATE oxconfig SET oxvarvalue = ENCODE( "'.$mystique.'", "'.$this->sConfigKey.'") WHERE oxvarname = "aModules"';
                    $ironman->query( $query );
                    break;
            }
            $ironman = null;
            return "ok";
        }
        catch ( PDOException $e )
        {
            die( "Error!: ".$e->getMessage() );
        }
        //$magneto = $wolverine->query( 'SELECT DECODE( oxvarvalue, "'.$this->sConfigKey.'") AS oxvarvalue FROM oxconfig WHERE oxvarname = "'.$sVarName.'"', PDO::FETCH_ASSOC )->fetch() or die ( "crap!" );

        //return unserialize($magneto["oxvarvalue"]);
    }


}

$catwoman = new superhero();
$batman = $catwoman->getAllModuleEntries();
if (!$batman->rowCount()) die( "no module data in mysql db found! Its time to call your programmer." );

foreach ($batman AS $robin)
{
    $name = $robin["oxvarname"];
    $type = $robin["oxvartype"];

    echo "<form name='form_".$name."' action='' method='post'>";
    echo "<table style='width: 50%; min-width: 750px; margin: 25px auto; border-collapse: collapse;' border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><td><b style='font-size: 150%;'>$name</b> ($type)";
    if ($_POST[$name] && "ok"==$catwoman->update( $name, $_POST[$name] )) echo "--- value saved ---";
    echo "</td><td width='150'><button type='submit' style='width: 100%; padding: 5px; background: green; color: white;'>SAVE</button></td></tr><tr><td colspan='2'>";

    $value = $catwoman->get($name);
    $rows = ( $type == "str" ) ? 1 : count( unserialize($value) )+1;


    if ( in_array($name, array("aModuleFiles","aModuleTemplates","aModuleEvents")) )
    {
        foreach (unserialize($value) AS $module => $items)
        {
            $rows = ( count( $items )>0 ) ? count( $items )+1 : 1;
            echo "<b style=\"color:orange;font-size: 125%;\">$module</b><textarea rows=\"$rows\" style=\"width:100%;\" name=\"".$name."[".$module."]\">";

            $errors = array();
            foreach ($items as $filename => $path)
            {
                echo "$filename => $path\n";
                if ($name!="aModuleEvents" && !file_exists( dirname( __FILE__ )."/modules/".$path )) $errors[] = "FILE NOT FOUND: $path";

            };
            echo "</textarea>";
            if (count( $errors )) echo "<div style=\"color:white;background:red;padding:3px;\">".implode( "<br/>", $errors )."</div>";
        }
    }
    else
    {
        echo "<textarea rows=\"$rows\" style=\"width:100%;\" name=\"$name\">";

        $errors = array();


        if ($type=="str" && $value) echo $value;
        if ($type=="arr" && count(unserialize($value ))) echo implode( "\n", unserialize($value ));
        if ($type=="aarr")
        {
            foreach (unserialize($value) as $key => $val)
            {
                echo $key." => ".$val."\n";
            }
        };
        if ($name=="aModules" && !file_exists( dirname( __FILE__ )."/modules/$val.php" )) $errors[] = "FILE NOT FOUND: $val.php";

        echo "</textarea>";
        if (count( $errors )) echo "<div style=\"color:white;background:red;padding:3px;\">".implode( "<br/>", $errors )."</div>";
    }

    echo "</td></tr></table></form>";

}


