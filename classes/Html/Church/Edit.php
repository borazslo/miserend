<?php

namespace Html\Church;

class Edit extends \Html\Html {

    public function __construct($path) {
        global $user;

        $this->input = $_REQUEST;
        $this->tid = $path[0];
        $this->church = \Eloquent\Church::find($this->tid);
        if (!$this->church) {
            throw new \Exception('Nincs ilyen templom.');
        }

        if (!$this->church->McheckWriteAccess($user)) {
            $this->title = 'Hiányzó jogosultság!';
            addMessage('Hiányzó jogosultság!', 'danger');
            return;
        }

        $isForm = \Request::Text('submit');
        if ($isForm) {
            $this->modify();
        }
        $this->preparePage();
    }

    function modify() {
        if ($this->input['church']['id'] != $this->tid) {
            throw new \Exception("Gond van a módosítandó templom azonosítójával.");
        }

        $allowedFields = ['adminmegj', 'kontakt', 'kontaktmail', 'nev',
            'ismertnev', 'orszag', 'megye', 'varos', 'cim', 'megkozelites',
            'egyhazmegye', 'espereskerulet', 'plebania', 'pleb_eml', 'pleb_url',
            'megjegyzes', 'miseaktiv', 'misemegj', 'leiras', 'ok', 'frissites'];

        global $user;
        if ($user->checkRole('miserend')) {
            $allowedFields[] = 'letrehozta';
        }

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $this->input['church'])) {
                $this->church->$field = $this->input['church'][$field];
            }
        }

        if (isset($this->input['photos'])) {
            foreach ($this->input['photos'] as $modPhoto) {
                $origPhoto = \Eloquent\Photo::find($modPhoto['id']);
                if ($origPhoto) {
                    if ($modPhoto['flag'] == 'i')
                        $origPhoto->flag = 'i';
                    else
                        $origPhoto->flag = "n";
                    if ($modPhoto['weight'] == '' OR is_numeric((int) $modPhoto['weight']))
                        $origPhoto->weight = $modPhoto['weight'];
                    else
                        $origPhoto->order = 0;
                    $origPhoto->title = $modPhoto['title'];
                    $origPhoto->save();
                    if ($modPhoto['delete']) {
                        $origPhoto->delete();
                    }
                }
            }
        }

        $this->church->log .= "\nMod: " . $user->login . " (" . date('Y-m-d H:i:s') . ")";
        $this->church->save();

        switch ($this->input['modosit']) {
            case 'n':
                $this->redirect("/church/catalogue");
                break;

            case 't':
                $this->redirect("/church/" . $this->church->id);
                break;

            case 'm':
                $this->redirect("/church/" . $this->church->id . "/editschedule");
                break;

            default:
                break;
        }
    }

    function preparePage() {

        global $user;
        if ($user->checkRole('miserend')) {
            $this->addFormResponsible();
        }

        $this->addFormAdministrative();
        $this->addFormReligiousAdministration();

        $this->church->photos;

        $this->form['ok'] = array(
            'name' => 'church[ok]',
            'options' => array(
                'i' => 'megjelenhet',
                'f' => 'áttekintésre vár',
                'n' => 'letiltva'
            ),
            'selected' => $this->church->ok
        );
        $this->title = $this->church->fullName;
    }

    function addFormAdministrative() {
        $options = [0 => 'Válassz/Nem tudom'];
        $countries = \Illuminate\Database\Capsule\Manager::table('orszagok')
                        ->select('id', 'nev')
                        ->orderBy('nev')->get();
        foreach ($countries as $selectibleCountry) {
            $options[$selectibleCountry->id] = $selectibleCountry->nev;
        }
        $this->form['country'] = array(
            'type' => 'select',
            'name' => 'church[orszag]',
            'id' => 'selectOrszag',
            'options' => $options,
            'selected' => $this->church->orszag
        );

        foreach ($countries as $selectibleCountry) {
            $options = [0 => 'Válassz/Nem tudom'];
            $counties = \Illuminate\Database\Capsule\Manager::table('megye')
                            ->select('id', 'megyenev', 'orszag')
                            ->where('orszag', $selectibleCountry->id)
                            ->orderBy('megyenev')->get();

            foreach ($counties as $selectibleCounty) {
                $options[$selectibleCounty->id] = $selectibleCounty->megyenev . " megye";
                $allCounties[] = $selectibleCounty;
            }

            $this->form['counties'][$selectibleCountry->id] = array(
                'type' => 'select',
                'name' => 'church[megye]',
                'id' => 'selectMegyeCountry' . $selectibleCountry->id,
                'class' => 'selectMegyeCountry',
                'data' => $selectibleCountry->id,
                'options' => $options,
                'selected' => $this->church->megye
            );
            if ($selectibleCountry->id == $this->church->orszag) {
                $this->form['counties'][$selectibleCountry->id]['style'] = 'display: inline';
            } else {
                $this->form['counties'][$selectibleCountry->id]['style'] = 'display: none';
                $this->form['counties'][$selectibleCountry->id]['disabled'] = 'disabled';
            }

            if (count($counties) < 1) {
                $extra = new \stdClass();
                $extra->id = 0;
                $extra->megyenev = '(Nincs megadva)';
                $extra->orszag = $selectibleCountry->id;
                $allCounties[] = $extra;
            }
        }

        foreach ($allCounties as $selectibleCounty) {
            $options = [0 => 'Válassz/Nem tudom'];
            $cities = \Illuminate\Database\Capsule\Manager::table('varosok')
                            ->select('id', 'nev')
                            ->where('orszag', $selectibleCounty->orszag)
                            ->where('megye_id', $selectibleCounty->id)
                            ->orderBy('nev')->get();
            foreach ($cities as $selectibleCity) {
                $options[$selectibleCity->nev] = $selectibleCity->nev;
            }
            $this->form['cities'][$selectibleCounty->orszag . "-" . $selectibleCounty->id] = array(
                'type' => 'select',
                'name' => 'church[varos]',
                'id' => 'selectVarosCounty' . $selectibleCounty->orszag . "-" . $selectibleCounty->id,
                'class' => 'selectVarosCounty',
                'options' => $options,
                'selected' => $this->church->varos
            );
            if ($selectibleCounty->id == $this->church->megye) {
                $this->form['cities'][$selectibleCounty->orszag . "-" . $selectibleCounty->id]['style'] = 'display: inline';
            } else {
                $this->form['cities'][$selectibleCounty->orszag . "-" . $selectibleCounty->id]['style'] = 'display: none';
                $this->form['cities'][$selectibleCounty->orszag . "-" . $selectibleCounty->id]['disabled'] = 'disabled';
            }
        }
    }

    function addFormReligiousAdministration() {
        $options = [0 => 'Válassz/Nem tudom'];
        $dioceses = \Illuminate\Database\Capsule\Manager::table('egyhazmegye')
                        ->select('id', 'nev')
                        ->orderBy('sorrend')->get();
        foreach ($dioceses as $selectibleDiocese) {
            $options[$selectibleDiocese->id] = $selectibleDiocese->nev;
        }
        $this->form['diocese'] = array(
            'type' => 'select',
            'name' => 'church[egyhazmegye]',
            'id' => 'selectEgyhazmegye',
            'options' => $options,
            'selected' => $this->church->egyhazmegye
        );

        foreach ($dioceses as $selectibleDiocese) {
            $options = [0 => 'Válassz/Nem tudom'];
            $deaneries = \Illuminate\Database\Capsule\Manager::table('espereskerulet')
                            ->select('id', 'nev')
                            ->where('ehm', $selectibleDiocese->id)
                            ->orderBy('nev')->get();
            foreach ($deaneries as $selectibleDeanery) {
                $options[$selectibleDeanery->id] = $selectibleDeanery->nev . " espereskerület";
            }
            $this->form['deaneries'][$selectibleDiocese->id] = array(
                'type' => 'select',
                'name' => 'church[espereskerulet]',
                'id' => 'selectEspereskeruletDiocese' . $selectibleDiocese->id,
                'class' => 'selectEspereskeruletDiocese',
                'options' => $options,
                'selected' => $this->church->espereskerulet
            );
            if ($selectibleDiocese->id == $this->church->egyhazmegye) {
                $this->form['deaneries'][$selectibleDiocese->id]['style'] = 'display: inline';
            } else {
                $this->form['deaneries'][$selectibleDiocese->id]['style'] = 'display: none';
                $this->form['deaneries'][$selectibleDiocese->id]['disabled'] = 'disabled';
            }
        }
    }

    function addFormResponsible() {
        $options = ['0' => 'Nincs megadva'];
        $users = \Illuminate\Database\Capsule\Manager::table('user')
                        ->select('login', 'uid')
                        ->where('ok', 'i')
                        ->orderByRaw("CASE WHEN lastlogin > '" . date('Y-m-d H:i:s', strtotime('-2 month')) . "'     THEN 1 ELSE 0 END desc")
                        ->orderBy('login')->get();
        foreach ($users as $selectibleUser) {
            $options[$selectibleUser->uid] = $selectibleUser->login;
        }
        $this->form['responsible'] = array(
            'type' => 'select',
            'name' => 'church[letrehozta]',
            'options' => $options,
            'default' => $this->church->feltolto
        );
    }

}
