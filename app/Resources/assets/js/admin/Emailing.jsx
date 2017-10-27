import React, { Component } from 'react'
import { func, object } from 'prop-types'
import Validator from 'validatorjs'
import { translate } from 'react-i18next'
import { basicNotification } from "../_components/Notifications"
import { handleErrors } from '../_utils/Utils'
import InputValidation from "../_components/InputValidation";
import { Button } from "../_components/Form";
import Editor from "../_components/Editor";

class Emailing extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props)
        this.state = {
            email: {
                sujet: "",
                message: ""
            },
            editState: false
        }
    }

    validationRules = {
        sujet: 'required',
        message: 'required'
    }

    handleChange = (name, value) => {
        const { email } = this.state
        email[name] = value
        this.setState({email})
        this.formIsValid(email)
    }

    formIsValid = (fields) => {
        const validation = new Validator(fields, this.validationRules)
        this.setState({editState: validation.passes()})
    }

    sendEmailing = () => {
        const { t, _addNotification } = this.context
        if (this.state.editState) {
            fetch(Routing.generate('sesile_main_emailingapi_post'), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    sujet: this.state.sujet,
                    message: this.state.message
                }),
                credentials: 'same-origin'
            })
                .then(handleErrors)
                .then(response => response.json())
                .then((json) => {
                    if (json === true) {
                        _addNotification(basicNotification(
                            'success',
                            t('admin.emailing.success_add')))
                    }
                    else {
                        _addNotification(basicNotification(
                            'warning',
                            json))
                    }
                })
                .catch(error => _addNotification(basicNotification(
                    'error',
                    t('admin.emailing.fail_add', {errorCode: error.status}),
                    error.statusText)))
        }
    }

    render() {

        const { t } = this.context
        const { email, editState } = this.state
        const isSuperAdmin = (this.props.user.roles.find(role => role.includes("ROLE_SUPER_ADMIN")) !== undefined)

        if (isSuperAdmin) {
            return (
                <div>
                    <h4 className="text-center text-bold">{t('admin.emailing.complet_name')}</h4>
                    <p className="text-center">{t('admin.emailing.subtitle')}</p>
                    <div className="grid-x align-center-middle">
                        <div className="cell medium-8">
                            <div className="grid-x grid-padding-x panel">
                                <div className="cell medium-12 panel-heading grid-x">
                                    <div className="cell medium-12">{t('admin.emailing.complet_name')}</div>
                                </div>
                                <div className="cell medium-12 panel-body">

                                    <InputValidation    id="sujet"
                                                        type="text"
                                                        className="cell medium-12"
                                                        labelText={t('admin.placeholder.subject')}
                                                        value={ email.sujet }
                                                        onChange={ this.handleChange }
                                                        validationRule={this.validationRules.sujet}
                                                        placeholder={t('admin.placeholder.subject')}/>

                                    <div className="grid-x grid-padding-x">
                                        <Editor id="message"
                                                label={t('admin.placeholder.message')}
                                                className="cell medium-12"
                                                value={ email.message }
                                                handleChange={ this.handleChange }
                                        />
                                    </div>

                                    <div className="grid-x grid-padding-x grid-padding-y grid-margin-y panel text-right">
                                        <Button id="submit-emailing"
                                                className="cell medium-12"
                                                classNameButton="primary"
                                                onClick={ this.sendEmailing }
                                                labelText={t('admin.button.send')}
                                                disabled={!editState}
                                        />
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            )
        }

        return (
            <div></div>
        )
    }
}

Emailing.propTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(Emailing)