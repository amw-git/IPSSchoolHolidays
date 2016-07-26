<?

class Schulferien extends IPSModule
{

    public function Create()
    {
        parent::Create();
        $this->RegisterPropertyString("Area", "baden-wuerttemberg");
        $this->RegisterPropertyString("BaseURL", "http://www.schulferien.org/media/ical/deutschland/ferien_");
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $this->RegisterVariableBoolean("IsSchoolHoliday", "Sind Ferien ?");
        $this->RegisterVariableString("SchoolHoliday", "Ferien");
        // 15 Minuten Timer
        $this->RegisterTimer("UpdateSchoolHolidays", 15 * 60 *1000, 'SCHOOL_Update($_IPS[\'TARGET\']);');
        // Nach übernahme der Einstellungen oder IPS-Neustart einmal Update durchführen.
        $this->Update();
    }

    private function GetFeiertag()
    {
        $jahr = date("Y") - 1;
        $link = $this->ReadPropertyString("BaseURL") . strtolower($this->ReadPropertyString("Area")) . "_" . $jahr . ".ics";
        $meldung = @file($link);
        if ($meldung === false)
            throw new Exception("Cannot load iCal Data.", E_USER_NOTICE);

        $jahr = date("Y");
        $link = $this->ReadPropertyString("BaseURL") . strtolower($this->ReadPropertyString("Area")) . "_" . $jahr . ".ics";
        $meldung2 = @file($link);
        if ($meldung2 === false)
            throw new Exception("Cannot load iCal Data.", E_USER_NOTICE);

        $meldung = array_merge($meldung, $meldung2);
        $ferien = "Keine Ferien";

        $anzahl = (count($meldung) - 1);

        for ($count = 0; $count < $anzahl; $count++)
        {
            if (strstr($meldung[$count], "SUMMARY:"))
            {
                $name = trim(substr($meldung[$count], 8));
                $start = trim(substr($meldung[$count + 1], 19));
                $ende = trim(substr($meldung[$count + 2], 17));
                $jetzt = date("Ymd") . "\n";
                if (($jetzt >= $start) and ( $jetzt <= $ende))
                {
                    $ferien = explode(' ', $name)[0];
                }
            }
        }
        return $ferien;
    }

    public function Update()
    {
        try
        {
            $holiday = $this->GetFeiertag();
        }
        catch (Exception $exc)
        {
            trigger_error($exc->getMessage(), $exc->getCode());
            return false;
        }


        $this->SetValueString("SchoolHoliday", $holiday);
        if ($holiday == "Keine Ferien")
        {
            $this->SetValueBoolean("IsSchoolHoliday", false);
        }
        else
        {
            $this->SetValueBoolean("IsSchoolHoliday", true);
        }
        return true;
    }

    private function SetValueBoolean($Ident, $value)
    {
        $id = $this->GetIDForIdent($Ident);
            SetValueBoolean($id, $value);
    }

    private function SetValueString($Ident, $value)
    {
        $id = $this->GetIDForIdent($Ident);
            SetValueString($id, $value);
    }

}

?>