<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=4 fdm=marker: */
/* mi: charset=Shift_JIS */

require_once 'Pager/Pager.php';

class Google_Renderer
{
    // {{{ properties

    /**
     * �������ʃu���b�N�̊J�n�^�O
     *
     * @var string
     * @access private
     */
    var $opener = '<table cellspacing="0" width="100%">';

    /**
     * �������ʁE�w�b�_
     *
     * @var string
     * @access private
     */
    var $header = '<tr class="tableheader">
    <td class="t">���</td>
    <td class="t">�^�C�g��</td>
    <td class="t">�͈�</td>
    <td class="t">��</td>
</tr>';

    /**
     * �������ʁE�e�A�C�e��
     *
     * @var string
     * @access private
     */
    var $body = '<tr class="%s">
    <td class="t">%s</td>
    <td class="t">%s</td>
    <td class="tn">%s</td>
    <td class="t">%s</td>
</tr>';

    /**
     * �������ʁE�G���[
     *
     * @var string
     * @access private
     */
    var $error = '<tr><td colspan="4" align="center">%s</td></tr>';

    /**
     * �������ʁE�t�b�^
     *
     * @var string
     * @access private
     */
    var $footer = '<tr class="tableheader">
    <td class="t" colspan="4" align="center">%d-%d / %d hits.</td>
</tr>';

    /**
     * �������ʃu���b�N�̏I���^�O
     *
     * @var string
     * @access private
     */
    var $closer = '</table>';

    // }}}
    // {{{ getRowClass()

    /**
     * ��s�������s���̎��ʎq��Ԃ�
     */
    function getRowClass()
    {
        static $i = 0;
        return (++$i % 2) ? 'r1' : 'r2';
    }

    // }}}
    // {{{ printSearchResult()

    /**
     * �������ʂ��o�͂���
     *
     * @return void
     * @access public
     */
    function printSearchResult(&$result, $word, $perPage, $start, $totalItems)
    {
        echo $this->opener;
        $this->printSearchResultHeader();
        if (is_array($result) && count($result) > 0) {
            foreach ($result as $id => $val) {
                $this->printSearchResultBody($id, $val, $this->getRowClass());
            }
        } elseif (is_string($result) && strlen($result) > 0) {
            printf($this->error, $result);
        }
        $this->printSearchResultFooter($perPage, $start, $totalItems);
        echo $this->closer;
    }

    // }}}
    // {{{ printSearchResultHeader()

    /**
     * �������ʂ̃w�b�_���o�͂���
     *
     * @return void
     * @access public
     */
    function printSearchResultHeader()
    {
        echo $this->header;
    }

    // }}}
    // {{{ printSearchResultBody()

    /**
     * �������ʂ̖{�̂��o�͂���
     *
     * @return void
     * @access public
     */
    function printSearchResultBody($id, $val, $rc)
    {
        $eh = "onmouseover=\"gShowPopUp('s%s',event)\" onmouseout=\"gHidePopUp('s%s')\"";
        $title = "<a class=\"thre_title\" href=\"%s\" {$eh} target=\"%s\" >%s</a>";

        $type_col  = $val['type'];
        $title_col = sprintf($title, $val['url'], $id, $id, $val['target'], $val['title']);
        $range_col = ($val['ls']  !== '') ? $val['ls']  : '&nbsp;';
        $ita_col   = ($val['ita'] !== '') ? $val['ita'] : '&nbsp;';

        printf($this->body, $rc, $type_col, $title_col, $range_col, $ita_col);
    }

    // }}}
    // {{{ printSearchResultFooter()

    /**
     * �������ʂ̃t�b�^���o�͂���
     *
     * @return void
     * @access public
     */
    function printSearchResultFooter($perPage, $start, $totalItems)
    {
        $from = ($totalItems > 0) ? ($start + 1) : 0;
        $to   = min($start + $perPage, $totalItems);

        printf($this->footer, $from, $to, $totalItems);
    }

    // }}}
    // {{{ printPopup()

    /**
     * �|�b�v�A�b�v�p�B���v�f���o�͂���
     *
     * @return void
     * @access public
     */
    function printPopup(&$popups)
    {
        if (!is_array($popups) || count($popups) == 0) {
            return;
        }

        $eh = "onmouseover=\"gShowPopUp('s%s',event)\" onmouseout=\"gHidePopUp('s%s')\"";
        $popup = "<div id=\"s%s\" class=\"respopup\" {$eh}>%s</div>\n";

        foreach ($popups as $id => $content) {
            printf($popup, $id, $id, $id, $content);
        }
    }

    // }}}
    // {{{ printPager()

    /**
     * �y�[�W�ړ��p�����N���o�͂���
     *
     * @return void
     * @access public
     */
    function printPager($perPage, $totalItems)
    {
        if (FALSE !== ($pager = &$this->makePager($perPage, $totalItems))) {
            echo '<table id="sbtoolbar2" class="toolbar" cellspacing="0"><tr><td align="center">';
            echo $pager->links;
            echo '</td></tr></table>';
        }
    }

    // }}}
    // {{{ makePager()

    /**
     * �������ʓ��ł̃y�[�W�ړ��p��PEAR::Pager�̃C���X�^���X���쐬����
     *
     * @return object
     * @access public
     */
    function &makePager($perPage, $totalItems)
    {
        if ($totalItems == 0 || $totalItems <= $perPage) {
            $retval = FALSE;
            return $retval;
        }

        $pagerOptions = array(
            'mode'       => 'Sliding',
            'totalItems' => $totalItems,
            'perPage'    => $perPage,
            'delta'      => 5, // �q�b�g�����y�[�W�O��̃����N�����y�[�W��
            'urlVar'     => 'p', // �y�[�WID����肷��GET/POST�̕ϐ����A�f�t�H���g��"PageID"
            'spacesBeforeSeparator' => 1,
            'spacesAfterSeparator'  => 1,
        );

        $pager = &Pager::factory($pagerOptions);

        return $pager;
    }

    // }}}
    // {{{ _rawurlencode_cb()

    /**
     * array_walk_recursive()�̃R�[���o�b�N���\�b�h�Ƃ��Ďg�p
     *
     * @return void
     * @access public
     */
    function _rawurlencode_cb(&$value, $key)
    {
        $value = rawurlencode($value);
    }

    // }}}
}