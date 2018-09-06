<?php



$files1 = scandir('./Structures/');    



for ($k=2; $k < sizeof($files1); $k++) { 

    $file_pieces = explode(".", $files1[$k]);

    $file_name = $file_pieces[0].'.'.$file_pieces[1];
    $myfile = fopen("./Structures/".$file_name, "r") or die("Unable to open file!");



if ($myfile) {
    $i = 0; 
    $clean_type = [];
    $clean_name = [];
    $ID_position = -1;

    while (($line = fgets($myfile)) !== false) {

        //echo $line.PHP_EOL;

        $pieces = explode("->", $line);

        $remove = [' ',';','}','\n'];

       if(isset($pieces[1]))
       {
                $clean_type[$i] = str_replace($remove, "", $pieces[0]);

               // echo $clean_type[$i];

                $boolean_field[$i] = 'false';
                $type[$i] = '?';

                switch($clean_type[$i])
                {
                case 'string':
                    $clean_type[$i] = 'FT_STRING';
                    $type[$i] = 's';
                    break;
                case 'int64':
                    $clean_type[$i] = 'FT_INT64';
                    $boolean_field[$i] = 'true';
                    $type[$i] = 'l';
                    break;
                case 'uint64':
                    $clean_type[$i] = 'FT_INT64'; 
                    $type[$i] = 'l';
                    break;     
                case 'int32':
                    $clean_type[$i] = 'FT_INT';
                    $boolean_field[$i] = 'true';
                    $type[$i] = 'i';
                    break;
                case 'uint32':
                    $clean_type[$i] = 'FT_INT'; 
                    $type[$i] = 'i';
                    break;  
                case 'Ref':
                    $clean_type[$i] = 'FT_INT'; 
                    $type[$i] = 'i';
                    break;         
                case 'int16':
                    $clean_type[$i] = 'FT_SHORT';
                    $boolean_field[$i] = 'true';
                    $type[$i] = 'h';
                    break;
                case 'uint16':
                    $clean_type[$i] = 'FT_SHORT';
                    $type[$i] = 'h';
                    break;    
                case 'int8':
                    $clean_type[$i] = 'FT_BYTE'; 
                    $boolean_field[$i] = 'true';
                    $type[$i] = 'b';
                    break;
                case 'uint8':
                    $clean_type[$i] = 'FT_BYTE'; 
                    $type[$i] = 'b';
                    break;    
                case 'float':
                    $clean_type[$i] = 'FT_FLOAT'; 
                    $type[$i] = 'f'; 
                    break;   
                default:
               // echo $clean_type[$i];
                $clean_type[$i] = 'FT_MISSING';  break;
                }


                $clean_name[$i] = str_replace($remove, "", $pieces[1]);

            
                $clean_name[$i] = str_replace(array("\n", "\r"), '', $clean_name[$i]);

                $type_array[$i] =  explode(",",  $clean_name[$i]);  

               if(isset($type_array[$i][1]))
               { 
                  //echo $type_array[$i][1].PHP_EOL; 
                  $type_array[$i] = $type_array[$i][1];
               }
               else
               {
                 $type_array[$i] = 1;  
               }   

                if($clean_name[$i] == 'Id')
                {
                   $ID_position = $i+1;  
                }


                $i++;
        }
       

    }

    fclose($myfile);
} else {
    // error opening the file.
} 

    //  DB2LoadInfo.h   
        $struct = "";

        for ($i=0; $i < sizeof($clean_name); $i++)
        { 
        $struct.='            { '.$boolean_field[$i].', '.$clean_type[$i].', "'.$clean_name[$i].'" },'.PHP_EOL;  
        }



        $result = 'struct '.$file_pieces[0].'LoadInfo
        {
            static DB2LoadInfo const* Instance()
            {
                static DB2FieldMeta const fields[] =
                {
        '.$struct.'
                };
                static DB2LoadInfo const loadInfo(&fields[0], std::extent<decltype(fields)>::value, '.$file_pieces[0].'Meta::Instance(), HOTFIX_SEL_ACHIEVEMENT);
                return &loadInfo;
            }
        };'.PHP_EOL.PHP_EOL;

        file_put_contents('./Output/DB2LoadInfo.txt', $result, FILE_APPEND);
        $result = '';    
    //  DB2LoadInfo.h   
    
    
// DB2Metadata.h


$types = "";
$array = $type_array[0];

      // Todo Array!  
        for ($i=0; $i < sizeof($clean_name); $i++)
        { 
        $types.=$type[$i]; 

        if($i > 0)
        {
        $array.= ', '.$type_array[$i].'';  
        }
        }
     // Todo Array
     
     



$result = 'struct '.$file_pieces[0].'Meta
{
    static DB2Meta const* Instance()
    {
        static char const* types = "'.$types.'";
        static uint8 const arraySizes['.sizeof($clean_name).'] = { '.$array.' };
        static DB2Meta instance('.$ID_position.', '.sizeof($clean_name).', 0x'.get_layout('./dbc/enUS/'.$file_pieces[0].'.db2').', types, arraySizes, -1);
        return &instance;
    }
};'.PHP_EOL.PHP_EOL;;

// DB2Metadata.h

//file_put_contents('./Output/DB2Metadata.txt', $result, FILE_APPEND);

}





function get_layout($filename)
{
    $fileHandle = @fopen($filename, 'rb');
    if ($fileHandle === false) {
        $layoutHash = '0xAAAAAAAA';
    }
    else
    {
        $fileFormat = fread($fileHandle, 4);

        $headerFormat = 'V9x/v2y/V7z';
        fseek($fileHandle, 4);
        $parts = array_values(unpack($headerFormat, fread($fileHandle, 68)));

        $layoutHash           = $parts[5];
    }

    if(strlen(dechex($layoutHash)) < 8)
    {
     //echo $filename.PHP_EOL;
     return '0'.strtoupper(dechex($layoutHash));
     /*
        ./dbc/enUS/Achievement_Category.db2
        ./dbc/enUS/AnimKit.db2
        ./dbc/enUS/BeamEffect.db2
        ./dbc/enUS/BroadcastTextSoundState.db2
        ./dbc/enUS/BroadcastTextVOState.db2
        ./dbc/enUS/Cfg_Regions.db2
        ./dbc/enUS/CharStartOutfit.db2
        ./dbc/enUS/ClientSceneEffect.db2
        ./dbc/enUS/ComponentModelFileData.db2
        ./dbc/enUS/ContentTuningDescription.db2
        ./dbc/enUS/Creature.db2
        ./dbc/enUS/CurrencyCategory.db2
        ./dbc/enUS/GameObjects.db2
        ./dbc/enUS/GarrEncounterXMechanic.db2
        ./dbc/enUS/GarrMechanicType.db2
        ./dbc/enUS/GarrMission.db2
        ./dbc/enUS/GarrMissionTexture.db2
        ./dbc/enUS/GlyphBindableSpell.db2
        ./dbc/enUS/InvasionClientData.db2
        ./dbc/enUS/ItemArmorQuality.db2
        ./dbc/enUS/ItemDisplayInfo.db2
        ./dbc/enUS/Light.db2
        ./dbc/enUS/ManagedWorldState.db2
        ./dbc/enUS/ManagedWorldStateBuff.db2
        ./dbc/enUS/ManifestInterfaceData.db2
        ./dbc/enUS/MinorTalent.db2
        ./dbc/enUS/PvpItem.db2
        ./dbc/enUS/Scenario.db2
        ./dbc/enUS/ScheduledInterval.db2
        ./dbc/enUS/SpellPowerDifficulty.db2
        ./dbc/enUS/SpellVisualMissile.db2
        ./dbc/enUS/TransmogSetGroup.db2
        ./dbc/enUS/TransportPhysics.db2
        ./dbc/enUS/Trophy.db2
        ./dbc/enUS/UiModelScene.db2
        ./dbc/enUS/UiTextureAtlasElement.db2
        ./dbc/enUS/UnitBlood.db2
        ./dbc/enUS/UnitTestSparse.db2
        ./dbc/enUS/WorldState.db2
     */
    }

   return  strtoupper(dechex($layoutHash));    
}