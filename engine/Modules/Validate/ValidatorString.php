<?php
/*-------------------------------------------------------
*
*   LiveStreet Engine Social Networking
*   Copyright © 2008 Mzhelskiy Maxim
*
*--------------------------------------------------------
*
*   Official site: www.livestreet.ru
*   Contact e-mail: rus.engine@gmail.com
*
*   GNU General Public License, version 2:
*   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*
---------------------------------------------------------
*/

namespace Engine\Modules\Validate;

/**
 * CStringValidator class file.
 *
 * @author    Qiang Xue <qiang.xue@gmail.com>
 * @link      http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license   http://www.yiiframework.com/license/
 */

use Engine\LS;
use Engine\Modules\ModuleLang;

/**
 * Валидатор текстовых данных на длину
 *
 * @package engine.modules.validate
 * @since   1.0
 */
class ValidatorString extends Validator
{
    /**
     * Максимальня длина строки
     *
     * @var int
     */
    public $max;
    /**
     * Минимальная длина строки
     *
     * @var int
     */
    public $min;
    /**
     * Конкретное значение длины строки
     *
     * @var int
     */
    public $is;
    /**
     * Кастомное сообщение об ошибке при короткой строке
     *
     * @var string
     */
    public $msgTooShort;
    /**
     * Кастомное сообщение об ошибке при слишком длинной строке
     *
     * @var string
     */
    public $msgTooLong;
    /**
     * Допускать или нет пустое значение
     *
     * @var bool
     */
    public $allowEmpty = true;

    /**
     * Запуск валидации
     *
     * @param mixed $sValue Данные для валидации
     *
     * @return bool|string
     */
    public function validate($sValue)
    {
        /** @var \Engine\Modules\ModuleLang $lang */
        $lang = LS::Make(ModuleLang::class);
        if (is_array($sValue)) {
            return $this->getMessage(
                $lang->Get('validate_string_too_short', null, false),
                'msgTooShort',
                ['min' => $this->min]
            );
        }
        if ($this->allowEmpty && $this->isEmpty($sValue)) {
            return true;
        }

        $iLength = mb_strlen($sValue, 'UTF-8');

        if ($this->min !== null && $iLength < $this->min) {
            return $this->getMessage(
                $lang->Get('validate_string_too_short', null, false),
                'msgTooShort',
                ['min' => $this->min]
            );
        }
        if ($this->max !== null && $iLength > $this->max) {
            return $this->getMessage(
                $lang->Get('validate_string_too_long', null, false),
                'msgTooLong',
                ['max' => $this->max]
            );
        }
        if ($this->is !== null && $iLength !== $this->is) {
            return $this->getMessage(
                $lang->Get('validate_string_no_lenght', null, false),
                'msg',
                ['length' => $this->is]
            );
        }

        return true;
    }
}
