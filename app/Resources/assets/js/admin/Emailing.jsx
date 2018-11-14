import React, { Component } from 'react'
import { object, func } from 'prop-types'
import { translate } from 'react-i18next'
import Validator from 'validatorjs'

import { basicNotification } from '../_components/Notifications'
import { Button, Form } from '../_components/Form'
import { GridX, Cell } from '../_components/UI'
import Editor from '../_components/Editor'
import {SimpleContent, AdminDetails, AdminPage} from '../_components/AdminUI'
import InputValidation from '../_components/InputValidation'

import { handleErrors } from '../_utils/Utils'

class Emailing extends Component {
    static contextTypes = {
        t: func,
        _addNotification: func
    }
    state = {
        validator: new Validator(),
        editState: false,
        sending: false,
        sujet: "",
        message: ""
    }
    validationRules = {
        sujet: 'required',
        message: 'required'
    }
    handleChangeSujet = (key, sujet) => {
        this.setState({sujet})
        this.formIsValid()
    }
    handleChangeMessage = (key, message) => {
        this.setState({message})
        this.formIsValid()
    }
    formIsValid = () => {
        const validation = new Validator({sujet: this.state.sujet, message: this.state.message}, this.validationRules)
        this.setState({editState: validation.passes()})
    }
    sendEmailing = () => {
        const { t, _addNotification } = this.context
        this.setState({sending: true, editState: false})
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
            .then(json => {
                if (json === true) {
                    _addNotification(basicNotification(
                        'success',
                        t('admin.emailing.success_sending')))
                    this.setState({sujet: '', message: ''})
                }
                else _addNotification(basicNotification('warning', json))
            })
            .catch(() =>
                _addNotification(basicNotification(
                    'error',
                    t('admin.emailing.error_sending'))))
            .finally(() => this.setState({sending: false, editState: true}))
        }
    }
    render() {
        const { t } = this.context
        return (
            <Form onSubmit={this.sendEmailing}>
                <AdminPage
                    title={t('admin.emailing.title')}
                >
                    <SimpleContent className="panel">
                        <GridX className="grid-padding-x grid-padding-y">
                            <Cell>
                                <InputValidation
                                    id="sujet"
                                    type="text"
                                    autoFocus={true}
                                    labelText={`${t('admin.emailing.subject')} *`}
                                    value={this.state.sujet}
                                    isValid={this.state.validator.passes() || true}
                                    errorMessage={this.state.validator.errors.get('sujet')}
                                    onChange={this.handleChangeSujet}
                                    validationRule={this.validationRules.sujet}
                                    placeholder={t('admin.emailing.type_subject')}/>
                            </Cell>
                            <Cell>
                                <Editor
                                    id="message"
                                    label={`${t('admin.placeholder.message')} *`}
                                    value={this.state.message}
                                    handleChange={this.handleChangeMessage}/>
                            </Cell>
                            <Button
                                id="submit-emailing"
                                className="cell medium-12 text-right"
                                classNameButton="primary"
                                loading={this.state.sending}
                                onClick={this.sendEmailing}
                                labelText={t('admin.button.send')}
                                disabled={!this.state.editState}/>
                        </GridX>
                    </SimpleContent>
                </AdminPage>
            </Form>
        )
    }
}

Emailing.propTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(Emailing)