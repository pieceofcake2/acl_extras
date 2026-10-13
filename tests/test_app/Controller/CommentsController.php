<?php
/**
 * Comments controller used by the AclExtras tests
 */
class CommentsController extends Controller
{
    /**
     * add action
     *
     * @return void
     */
    public function add()
    {
    }

    /**
     * index action
     *
     * @return void
     */
    public function index()
    {
    }

    /**
     * delete action
     *
     * @return void
     */
    public function delete()
    {
    }

    /**
     * _secret_method action
     *
     * Public on purpose: AclExtras must skip actions prefixed with an underscore.
     *
     * @return void
     */
    public function _secret_method() // phpcs:ignore CakePHP.NamingConventions.ValidFunctionName.PublicWithUnderscore
    {
    }
}
