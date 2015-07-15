<?php
namespace Jobs\Model\Process\DataTransfer\Acquisition\Foo\Bar\Dummy;

use Minibus\Model\Process\Conversion\IConverter;

class Converter implements IConverter
{

    private $errors;

    private $elementPool;

    public function __construct()
    {
        $this->elementPool = new \ArrayObject();
    }

    public function convert($arrayOrObject)
    {
        throw new \Exception("Not implemented");
    }

    public function getFields()
    {
        throw new \Exception("Not implemented");
    }

    /**
     *
     * @param ProgramType $parcours            
     * @throws InvalidRofElementException
     * @return \Jobs\Model\Process\DataTransfer\Acquisition\Formations\MasterParis\Rof\Parcours
     */
    public function convertToAnneeEntity(ProgramType $parcours, $mentionRofId, $mentionCode)
    {
        $this->resetErrors();
        if (is_null($parcours))
            throw new InvalidRofElementException("Le parcours fourni est nul.");
        $anneeId = $this->generateIdentifierForParcoursAnnee($parcours, $mentionRofId);
        if ($this->elementPool->offsetExists($anneeId))
            return $this->elementPool->offsetGet($anneeId);
        $parcoursName = $this->getParcoursName($parcours);
        $intituleAnnee = $this->splitParcoursName($parcoursName)['annee'];
        
        $parcoursPed = new Parcours();
        $parcoursPed->setLibelle($intituleAnnee);
        $parcoursPed->setRofId($mentionRofId . '-' . $intituleAnnee);
        $parcoursPed->setType('annee');
        $parcoursPed->setCode($mentionCode . $intituleAnnee);
        $this->elementPool->offsetSet($anneeId, $parcoursPed);
        return $parcoursPed;
    }

    public function convertToSemestreEntity(ProgramType $parcours)
    {
        $this->resetErrors();
        if (is_null($parcours))
            throw new InvalidRofElementException("Le parcours fourni est nul.");
        $parcoursId = $this->getIdProgram($parcours);
        if ($this->elementPool->offsetExists($parcoursId))
            return $this->elementPool->offsetGet($parcoursId);
        $parcoursName = $this->getParcoursName($parcours);
        $parcoursPed = new Parcours();
        $parcoursPed->setLibelle($parcoursName);
        $parcoursPed->setRofId($parcoursId);
        $parcoursPed->setType('semestre');
        $parcoursPed->setCode($parcours->getIdent());
        $this->elementPool->offsetSet($parcoursId, $parcoursPed);
        return $parcoursPed;
    }

    private function generateIdentifierForParcoursAnnee(ProgramType $parcours, $mentionId)
    {
        $parcoursName = $this->getParcoursName($parcours);
        $intituleAnnee = $this->splitParcoursName($parcoursName)['annee'];
        return $mentionId . '-' . $intituleAnnee;
    }

    /**
     *
     * @param unknown $anneeScolaire            
     * @param ProgramType $parcours            
     * @throws InvalidRofElementException
     * @return boolean
     */
    public function convertToSpecialisationEntity(ProgramType $parcours)
    {
        $this->resetErrors();
        if (is_null($parcours))
            throw new InvalidRofElementException("Le parcours fourni est nul.");
        $parcoursId = $this->getIdProgram($parcours);
        if ($this->elementPool->offsetExists($parcoursId))
            return $this->elementPool->offsetGet($parcoursId);
        $parcoursName = $this->getParcoursName($parcours);
        $intitule = $this->splitParcoursName($parcoursName)['specialisation'];
        $parcoursPed = new Parcours();
        $parcoursPed->setLibelle($intitule);
        $parcoursPed->setRofId($parcoursId);
        $parcoursPed->setType('specialisation');
        $parcoursPed->setCode($parcours->getIdent());
        $this->elementPool->offsetSet($parcoursId, $parcoursPed);
        return $parcoursPed;
    }

    public function convertToBlocEntity(ProgramType $parcours)
    {
        $this->resetErrors();
        if (is_null($parcours))
            throw new InvalidRofElementException("Le parcours fourni est nul.");
        $parcoursId = $this->getIdProgram($parcours);
        if ($this->elementPool->offsetExists($parcoursId))
            return $this->elementPool->offsetGet($parcoursId);
        $parcoursName = $this->getParcoursName($parcours);
        $parcoursPed = new Parcours();
        $parcoursPed->setLibelle($parcoursName);
        $parcoursPed->setRofId($parcoursId);
        $parcoursPed->setType('bloc');
        $parcoursPed->setCode($parcours->getIdent());
        $this->elementPool->offsetSet($parcoursId, $parcoursPed);
        return $parcoursPed;
    }

    public function convertToCourseEntity(CourseType $course)
    {
        $this->resetErrors();
        if (is_null($course))
            throw new InvalidRofElementException("Le cours fourni est nul.");
        $courseId = $this->getIdCours($course);
        if ($this->elementPool->offsetExists($courseId))
            return $this->elementPool->offsetGet($courseId);
        $courseName = substr($this->getCourseName($course), 0, 150);
        $ects = 0;
        $ects = $this->getEcts($course);
        if (false == $ects)
            $this->addError($courseId, "Le cours  $courseId n'a pas d'ects sur ses éléments credits.");
        
        $code = $this->getCode($course);
        if (false === $code)
            throw new InvalidRofElementException("Le cours $courseId fourni n'a pas de code (attribut ident).");
        $langue = $this->getLangue($course);
        if (false == $langue) {
            $this->addError($courseId, "Le cours $courseId fourni ne précise pas la langue dans laquelle il est dispensé.");
            $langue = "";
        }
        $motsCles = $this->getMotsCles($course);
        $capacite = $this->getCapacite($course);
        if (false == $capacite) {
            $capacite = 0;
        }
        $modaliteEnseignement = $this->getFormOfTeaching($course);
        if (false == $modaliteEnseignement)
            $modaliteEnseignement = '';
        $prerequisRecommandes = $this->getRecommendedPrerequisites($course);
        if (false == $prerequisRecommandes)
            $prerequisRecommandes = '';
        $prerequisObligatoires = $this->getFormalPrerequisites($course);
        if (false == $prerequisObligatoires)
            $prerequisObligatoires = '';
        $controleConnaissances = $this->getFormOfAssessment($course);
        if (false == $controleConnaissances)
            $controleConnaissances = '';
        $learningObjectives = $this->getLearningObjectives($course);
        if (false == $learningObjectives)
            $learningObjectives = '';
        $description = $this->getDescription($course);
        if (false == $description) {
            $this->addError($courseId, "Le cours $courseId fourni n'a pas de description.");
            $description = '';
        }
        $discipline = $this->getDiscipline($course);
        if (false == $discipline) {
            $this->addError($courseId, "Le cours $courseId fourni n'a pas de discipline.");
            $discipline = '';
        }
        $dureeCm = $this->getDureeCM($course);
        if (false == $dureeCm)
            $dureeCm = null;
        $dureeTd = $this->getDureeTD($course);
        if (false == $dureeTd)
            $dureeTd = null;
        $dureeTp = $this->getDureeTP($course);
        if (false == $dureeTp)
            $dureeTp = null;
        $uePed = new UE();
        $uePed->setLibelle($courseName);
        $uePed->setRofId($courseId);
        $uePed->setEcts($ects);
        $uePed->setCode($code);
        $uePed->setMotscles($motsCles);
        $uePed->setCapacite($capacite);
        $uePed->setModaliteEnseignement($modaliteEnseignement);
        $uePed->setLangueEnseignement($langue);
        $uePed->setDescription($description);
        $uePed->setCompetences($learningObjectives);
        $uePed->setDisciplines($discipline);
        $uePed->setPrerequisObligatoires($prerequisObligatoires);
        $uePed->setPrerequisRecommandes($prerequisRecommandes);
        $uePed->setControleConnaissances($controleConnaissances);
        $uePed->setDureeCm($dureeCm);
        $uePed->setDureeTd($dureeTd);
        $uePed->setDureeTp($dureeTp);
        $this->elementPool->offsetSet($courseId, $uePed);
        
        return $uePed;
    }

    private function getIdProgram(ProgramType $parcours)
    {
        $programIds = $parcours->getProgramID();
        if (empty($programIds))
            throw new InvalidRofElementException("Le parcours fourni n'a pas d'identifiant.");
        return rtrim($programIds[0]);
    }

    private function getIdCours(CourseType $course)
    {
        $courseIds = $course->getCourseID();
        if (empty($courseIds))
            throw new InvalidRofElementException("Le cours fourni n'a pas d'identifiant.");
        return rtrim($courseIds[0]);
    }

    private function getEcts(CourseType $course)
    {
        $credits = $course->getCredits();
        if (empty($credits))
            return false;
        foreach ($credits as $credit) {
            $ects = rtrim($credit->getECTScredits());
            
            if (is_numeric($ects) && $ects > 0) {
                
                return $ects;
            }
        }
        return false;
    }

    public function getLangue(CourseType $course)
    {
        $instructionLanguages = $course->getInstructionLanguage();
        if (empty($instructionLanguages))
            return false;
        foreach ($instructionLanguages as $instructionLanguage) {
            $langue = rtrim($instructionLanguage->getTeachingLang());
            
            if (! empty($langue)) {
                
                return $langue;
            }
        }
        return false;
    }

    private function getCapacite(CourseType $course)
    {
        $admissionInfos = $course->getAdmissionInfo();
        if (empty($admissionInfos))
            return false;
        
        foreach ($admissionInfos as $admissionInfo) {
            
            $places = $admissionInfo->getStudentPlaces();
            if (empty($places))
                return false;
            foreach ($places as $place) {
                $capacite = rtrim($place->getPlaces());
                if (is_numeric($capacite) && $capacite > 0)
                    return $capacite;
            }
        }
        return false;
    }

    private function getMotsCles(CourseType $course)
    {
        return $course->getSearchword();
    }

    private function getCode(CourseType $course)
    {
        $code = $course->getIdent();
        if (empty($code))
            return false;
        return rtrim($code);
    }

    private function getFormOfTeaching(CourseType $course)
    {
        $formes = $course->getFormOfTeaching();
        if (empty($formes))
            return false;
        foreach ($formes as $forme) {
            if (! empty(rtrim(strip_tags($forme))))
                return rtrim(strip_tags($forme));
        }
        return false;
    }

    private function getFormOfAssessment(CourseType $course)
    {
        $formes = $course->getFormOfAssessment();
        if (empty($formes))
            return false;
        foreach ($formes as $forme) {
            if (! empty(rtrim(strip_tags($forme))))
                return rtrim(strip_tags($forme));
        }
        return false;
    }

    private function getRecommendedPrerequisites(CourseType $course)
    {
        $prereqs = $course->getRecommendedPrerequisites();
        if (empty($prereqs))
            return false;
        foreach ($prereqs as $prereq) {
            if (! empty(rtrim(strip_tags($prereq))))
                return rtrim(strip_tags($prereq));
        }
        return false;
    }

    private function getFormalPrerequisites(CourseType $course)
    {
        $prereqs = $course->getFormalPrerequisites();
        if (empty($prereqs))
            return false;
        foreach ($prereqs as $prereq) {
            if (! empty(rtrim(strip_tags($prereq))))
                return rtrim(strip_tags($prereq));
        }
        return false;
    }

    private function getLearningObjectives(CourseType $course)
    {
        $objectives = $course->getLearningObjectives();
        if (empty($objectives))
            return false;
        foreach ($objectives as $objective) {
            if (! empty(rtrim(strip_tags($objective))))
                return rtrim(strip_tags($objective));
        }
        return false;
    }

    private function getDiscipline(CourseType $course)
    {
        $intros = $course->getCourseIntroduction();
        if (empty($intros))
            return false;
        foreach ($intros as $intro) {
            if (! empty(rtrim(strip_tags($intro))))
                return rtrim(strip_tags($intro));
        }
        return false;
    }

    private function getDureeCM(CourseType $course)
    {
        return $this->getDuree($course, 'CM');
    }

    private function getDureeTP(CourseType $course)
    {
        return $this->getDuree($course, 'TP');
    }

    private function getDureeTD(CourseType $course)
    {
        return $this->getDuree($course, 'TD');
    }

    private function getDuree(CourseType $course, $teachingType)
    {
        $credits = $course->getCredits();
        if (empty($credits))
            return false;
        foreach ($credits as $credit) {
            $globalVolumes = $credit->getGlobalVolume();
            if (empty($globalVolumes))
                continue;
            foreach ($globalVolumes as $globalVolume) {
                if ($globalVolume->getTeachingtype() == $teachingType) {
                    return floatval($globalVolume->value());
                }
            }
        }
        return false;
    }

    private function getDescription(CourseType $course)
    {
        $descriptions = $course->getCourseDescription();
        if (empty($descriptions))
            return false;
        foreach ($descriptions as $description) {
            $content = $description->getInfoBlock();
            
            if (count($content) > 0) {
                if (! empty(rtrim(strip_tags($content[0]))))
                    return rtrim(rtrim(strip_tags($content[0])));
            }
        }
        return false;
    }

    private function getParcoursName(ProgramType $parcours)
    {
        $programNames = $parcours->getProgramName();
        if (empty($programNames))
            throw new InvalidRofElementException("Le parcours fourni n'a pas de programname :" . $this->getIdProgram($parcours));
        $programNameText = $programNames[0]->getText();
        if (empty($programNameText))
            throw new InvalidRofElementException("Le parcours fourni n'a pas de texte sous son programName." . $this->getIdProgram($parcours));
        return rtrim($programNameText[0]);
    }

    private function getCourseName(CourseType $course)
    {
        $courseNames = $course->getCourseName();
        if (empty($courseNames))
            throw new InvalidRofElementException("Le course fourni n'a pas de coursename.");
        return rtrim($courseNames[0]);
    }

    private function splitParcoursName($parcoursName)
    {
        $matches = array();
        // suppression des sauts de ligne
        $parcoursName = preg_replace('~[[:cntrl:]]~', '', $parcoursName);
        preg_match("/^(M[12])\s*-\s*(.+)\s*$/", $parcoursName, $matches);
        if (empty($matches))
            throw new InvalidRofElementException("Le parcours fourni ne peut pas être décomposé en semestre/spécialisation : " . $parcoursName);
        return array(
            'annee' => $matches[1],
            'specialisation' => $matches[2]
        );
    }

    private function resetErrors()
    {
        $this->errors = array();
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return ! empty($this->errors);
    }

    public function addError($objectidentifier, $message)
    {
        if (! array_key_exists($objectidentifier, $this->errors))
            $this->errors[$objectidentifier] = array();
        $this->errors[$objectidentifier][] = $message;
    }
}