<?php
/**
 * \Elabftw\Elabftw\ExperimentsView
 *
 * @author Nicolas CARPi <nicolas.carpi@curie.fr>
 * @copyright 2012 Nicolas CARPi
 * @see https://www.elabftw.net Official website
 * @license AGPL-3.0
 * @package elabftw
 */
namespace Elabftw\Elabftw;

use Exception;
use Datetime;

/**
 * Experiments View
 */
class ExperimentsView extends EntityView
{
    /** Read only switch */
    public $ro = false;

    /** instance of TeamGroups */
    public $TeamGroups;

    /**
     * Need an instance of Experiments
     *
     * @param Entity $entity
     * @throws Exception
     */
    public function __construct(Entity $entity)
    {
        $this->Entity = $entity;
        $this->limit = $this->Entity->Users->userData['limit_nb'];
        $this->showTeam = $this->Entity->Users->userData['show_team'];

        $this->TeamGroups = new TeamGroups($this->Entity->Users->userData['team']);
    }

    /**
     * View experiment
     *
     * @return string HTML for viewXP
     */
    public function view()
    {
        $this->html .= $this->UploadsView->buildUploads('view');
        $this->html .= $this->buildComments();
        $this->html .= $this->buildCommentsCreate();

        return $this->html;
    }

    /**
     * If int, get the name of the team group instead of a number
     *
     * @return string
     */
    public function getVisibility()
    {
        if (Tools::checkId($this->Entity->entityData['visibility'])) {
            return $this->TeamGroups->readName($this->Entity->entityData['visibility']);
        }
        return ucfirst($this->Entity->entityData['visibility']);
    }

    /**
     * Show info on timestamp
     *
     * @return string
     */
    public function showTimestamp()
    {
        $Users = new Users();
        $timestamper = $Users->read($this->Entity->entityData['timestampedby']);

        $this->UploadsView->Uploads->Entity->type = 'exp-pdf-timestamp';
        $pdf = $this->UploadsView->Uploads->readAll();

        $this->UploadsView->Uploads->Entity->type = 'timestamp-token';
        $token = $this->UploadsView->Uploads->readAll();

        // set correct type back
        $this->UploadsView->Uploads->Entity->type = 'experiments';

        $date = new DateTime($this->Entity->entityData['timestampedwhen']);

        return Tools::displayMessage(
            _('Experiment was timestamped by') . " " . $timestamper['fullname'] . " " . _('on') .
            " " . $date->format('Y-m-d') . " " . _('at') . " " .
            $date->format('H:i:s') . " " .
            $date->getTimezone()->getName() . " <a href='uploads/" .
            $pdf[0]['long_name'] . "'><img src='app/img/pdf.png' title='" .
            _('Download timestamped pdf') . "' alt='pdf' /></a> <a href='uploads/" . $token[0]['long_name'] .
            "'><img src='app/img/download.png' title=\"" . _('Download token') .
            "\" alt='download token' /></a> <a href='#'><img onClick=\"decodeAsn1('" . $token[0]['long_name'] .
            "', '" . $this->Entity->entityData['id'] . "')\" src='app/img/info.png' title=\"" . _('Decode token') .
            "\" alt='decode token' /></a><div style='color:black;overflow:auto;display:hidden' id='decodedDiv'></div>",
            'ok',
            false
        );
    }

    /**
     * Display comments for an experiment
     *
     */
    private function buildComments()
    {
        $Comments = new Comments($this->Entity);
        $commentsArr = $Comments->read();

        //  we need to add a container here so the reload function in the callback of .editable() doesn't mess things up
        $html = "<section id='expcomment_container'>";
        $html .= "<div id='expcomment' class='box'>";
        $html .= "<h3><img src='app/img/comment.png' alt='comment' />" . _('Comments') . "</h3>";

        if (is_array($commentsArr)) {
            // there is comments to display
            foreach ($commentsArr as $comment) {
                if (empty($comment['firstname'])) {
                    $comment['firstname'] = '[deleted]';
                }
                $html .= "<div class='expcomment_box'>
                    <img class='align_right' src='app/img/small-trash.png' ";
                $html .= "title='delete' alt='delete' onClick=\"commentsDestroy(" .
                    $comment['id'] . ", " . $this->Entity->id . ", '" . _('Delete this?') . "')\" />";
                $html .= "<span>On " . $comment['datetime'] . " " . $comment['firstname'] . " " .
                    $comment['lastname'] . " wrote :</span><br />";
                $html .= "<p class='editable' id='" . $comment['id'] . "'>" . $comment['comment'] . "</p></div>";
            }
        }
        return $html;
    }

    /**
     * HTML for the add new comment block
     */
    private function buildCommentsCreate()
    {
        $html = "<textarea onFocus='commentsCreateButtonDivShow()' id='commentsCreateArea' placeholder='" .
            _('Add a comment') . "'></textarea>";
        $html .= "<div id='commentsCreateButtonDiv' class='submitButtonDiv'>";
        $html .= "<button class='button' id='commentsCreateButton' onClick='commentsCreate(" .
            $this->Entity->id . ")'>" . _('Save') . "</button></div></div></section>";

        return $html;
    }
}
