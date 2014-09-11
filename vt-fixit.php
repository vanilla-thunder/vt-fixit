<?php
class superhero
{
    function __construct()
    {
        include( dirname(__FILE__) . "/config.inc.php");
        include( dirname(__FILE__) . "/core/oxconfk.php");
    }

    function getVar($var)
    {
        return $this->$var;
    }
}

$catwoman = new superhero();

//$db = mysqli_connect($cfg->dbHost,$cfg->dbUser,$cfg->dbPwd,$cfg->dbName) or die("error connecting mysql db: ". mysqli_error($db));

try
{
    $superman = new PDO('mysql:host='.$catwoman->dbHost.';dbname='.$catwoman->dbName, $catwoman->dbUser, $catwoman->dbPwd);
    $batman = $superman->query('SELECT oxvarname, oxvartype, DECODE( oxvarvalue, "'.$catwoman->sConfigKey.'") AS oxvarvalue FROM oxconfig WHERE oxvarname LIKE "%module%"',PDO::FETCH_ASSOC) or die ("crap!");

    if(!$batman->rowCount()) die("no module data in mysql db found! Its time to call your programmer.");


    $robin = $batman->fetchAll();



    foreach($robin AS $row)
    {
        $name = $row["oxvarname"];
        $type = $row["oxvartype"];
        $oxvarvalue = unserialize($row["oxvarvalue"]);
        $rows = (count($oxvarvalue) > 0) ? count($oxvarvalue)+1 : 1;

        echo "<form name='edit_".$name."' action='vt-fix.php' method='post'>";
        echo "<table style='width: 50%; min-width: 750px; margin: 25px auto; border-collapse: collapse;' border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><td><b style='font-size: 150%;'>$name ($type)</b></td><td width='150'><button type='submit' style='width: 100%; padding: 5px; background: green; color: white;'>SAVE</button></td></tr><tr><td colspan='2'>";

        if($name == "aModuleFiles" || $name == "aModuleTemplates" || $name == "aModuleEvents")
        {
            foreach($oxvarvalue AS $module => $items)
            {
                $rows = (count($items) > 0) ? count($items) : 1;
                echo "<b style=\"color:orange;font-size: 125%;\">$module</b><textarea rows=\"$rows\" style=\"width:100%;\" name=\"$module\">";

                $errors = array();
                foreach($items as $filename => $path)
                {
                    echo "$filename => $path\n";
                    if($name != "aModuleEvents" && !file_exists(dirname(__FILE__)."/modules/".$path)) $errors[] = "FILE NOT FOUND: $path";

                };
                echo"</textarea>";
                if(count($errors)) echo "<div style=\"color:white;background:red;padding:3px;\">".implode("<br/>",$errors)."</div>";
            }
        }
        else
        {
            echo "<textarea rows=\"$rows\" style=\"width:100%;\" name=\"$name\">";

            $errors = array();

            if($type == "arr") echo implode("\n",$oxvarvalue);
            if($type == "aarr") foreach($oxvarvalue as $key => $val) { echo $key." => ". $val ."\n"; };
            if($name == "aModules" && !file_exists(dirname(__FILE__)."/modules/$val.php")) $errors[] = "FILE NOT FOUND: $val.php";

            echo"</textarea>";
            if(count($errors)) echo "<div style=\"color:white;background:red;padding:3px;\">".implode("<br/>",$errors)."</div>";
        }

        echo "</td></tr></table></form>";

    }

    $superman = null;
}
catch (PDOException $e)
{
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}

