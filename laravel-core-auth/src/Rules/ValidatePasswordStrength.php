<?php

namespace AttractCores\LaravelCoreAuth\Rules;

use AttractCores\LaravelCoreKit\Models\Permission;
use AttractCores\LaravelCoreKit\Models\User;
use Illuminate\Contracts\Validation\Rule;

class ValidatePasswordStrength implements Rule
{
    protected int $requiredNumbers;

    protected int $requiredUppercaseChars;
    protected int $requiredLowercaseChars;

    protected int $requiredSpecificChars;

    protected string $specificChars;

    /**
     * Create a new rule instance.
     *
     * @param int    $requiredNumbers
     * @param int    $requiredUppercaseChars
     * @param int    $requiredLowercaseChars
     * @param int    $requiredSpecificChars
     * @param string $specificChars
     */
    public function __construct(
        int $requiredNumbers = 1, int $requiredUppercaseChars = 1,
        int $requiredLowercaseChars = 1,
        int $requiredSpecificChars = 1, string $specificChars = "\_\=\+\-\:\"\'\?\[\]\{\}\!\@\#\$\%\^\&\*\(\)\."
    )
    {

        $this->requiredNumbers = $requiredNumbers;
        $this->requiredUppercaseChars = $requiredUppercaseChars;
        $this->requiredLowercaseChars = $requiredLowercaseChars;
        $this->requiredSpecificChars = $requiredSpecificChars;
        $this->specificChars = $specificChars;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $result = true;

        $result = $result && preg_match('/[0-9]{' . $this->requiredNumbers . ',}/u', $value);
        $result = $result && preg_match("/[A-ZА-Я]{" . $this->requiredUppercaseChars . ",}/u", $value);
        $result = $result && preg_match("/[a-za-я]{" . $this->requiredLowercaseChars . ",}/u", $value);
        $result = $result && preg_match("/[" . $this->specificChars . "]{" . $this->requiredUppercaseChars . ",}/u", $value);

        return $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __("Password should include at least one lowercase letter, one upper case letter, one special symbol and a number.");
    }

}
