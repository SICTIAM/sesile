import React, { Component } from 'react'
import { func, object } from 'prop-types'
import { translate } from 'react-i18next'
import { Link } from 'react-router-dom'
import Validator from 'validatorjs'
import { basicNotification } from "../_components/Notifications"
import { handleErrors } from '../_utils/Utils'
import InputValidation from "../_components/InputValidation"
import {Button, InputFile, InputDatePicker} from '../_components/Form'
import Moment from 'moment'

class DocumentationPatch extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props)
        this.state = {
            patch: {},
            editState: false
        }
    }

    componentDidMount() {
        this.state.patch = this.props.patch
    }

    componentWillReceiveProps(nextProps) {
        if (this.props.patch !== nextProps.patch) {
            this.setState({patch: nextProps.patch})
        }
    }

    validationRules = {
        id: 'required',
        description: 'required',
        version: 'required',
        date: 'required'
    }

    updateDocument = (file) => {
        if (file) {
            const data = new FormData()
            data.append('path', file)
            fetch(Routing.generate('sesile_main_documentationapi_uploadpatchdocument', {id: this.state.patch.id}), {
                credentials: 'same-origin',
                method: 'POST',
                body: data
            })
                .then(handleErrors)
                .then(response => response.json())
                .then(patch => {
                    this.context._addNotification(basicNotification(
                        'success',
                        this.context.t('admin.collectivite.success_upload_avatar')))
                    this.setState({patch})}
                )
                .catch(error => this.context._addNotification(basicNotification(
                   'error',
                   this.context.t('admin.collectivite.error.upload_avatar', {errorCode: error.status}),
                   error.statusText)))
        }
    }

    handleChange = (name, value) => {
        const { patch } = this.state
        patch[name] = value
        this.setState({patch})
        this.formIsValid(patch)
    }

    handleChangeDate = (date) => {
        const { patch } = this.state
        patch['date'] = date
        this.setState({patch})
        this.formIsValid(patch)
    }

    formIsValid = (fields) => {
        const validation = new Validator(fields, this.validationRules)
        this.setState({editState: validation.passes()})

        return validation.passes()
    }

    updatePatch = () => {
        const { id, description, version, date } = this.state.patch

        if(this.formIsValid(this.state.patch)) {

            fetch(Routing.generate('sesile_main_documentationapi_updatepatch', {id}), {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    description: description,
                    date: Moment(date).format('YYYY-MM-DD HH:mm'),
                    version: version
                }),
                credentials: 'same-origin'
            })
                .then(handleErrors)
                .then(response => response.json())
                .then(patch => {
                    this.context._addNotification(basicNotification(
                        'success',
                        this.context.t('admin.documentations.success_edit')))
                    this.setState(patch)
                })
                .catch(error => this.context._addNotification(basicNotification(
                    'error',
                    this.context.t('admin.documentations.error.fail_edit', {errorCode: error.status}),
                    error.statusText)))
        }
    }

    deleteFile = () => {
        const { t, _addNotification } = this.context
        const { patch } = this.state

        fetch(Routing.generate('sesile_main_documentationapi_deletepatchdocument', {id: patch.id}), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then(patch => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.success.delete', {name: t('admin.documentations.patch')})
                ))
                this.setState({patch})
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.documentations.error.delete_document', {errorCode: error.status}),
                error.statusText)))
    }

    delete = () => {
        this.props.delete(this.props.patch.id)
    }

    render() {

        const { patch, editState } = this.state
        const { t } = this.context
        const { i18nextLng } = window.localStorage
        return (
            <div className="grid-x grid-padding-x grid-padding-y">
                <InputValidation    id="description"
                                    type="text"
                                    className="medium-2 cell"
                                    labelText={t('common.label.description')}
                                    value={ patch.description }
                                    onChange={this.handleChange}
                                    validationRule={this.validationRules.description}
                                    placeholder={t('admin.documentations.placeholder_description')}/>

                <InputValidation    id="version"
                                    type="text"
                                    className="medium-1 cell"
                                    labelText={t('admin.documentations.label_version')}
                                    value={ patch.version }
                                    onChange={this.handleChange}
                                    validationRule={this.validationRules.description}
                                    placeholder={t('admin.documentations.placeholder_version')}/>

                <InputDatePicker
                    label={ t('admin.documentations.label_date') }
                    date={ Moment(patch.date) }
                    onChange={ this.handleChangeDate }
                    locale={ i18nextLng }
                    className="medium-2 cell"
                />

                <div className="cell medium-5">
                    <div className="grid-x">
                        {
                            patch.path &&
                            <div className="cell auto text-center">
                                <Link to={"/uploads/docs/" + patch.path} className="button primary" target="_blank">{t('common.help_board.view_button')}</Link>
                            </div>
                        }
                    </div>
                    <div className="grid-x grid-margin-y grid-padding-y">
                        <InputFile  id={"patch-image" + patch.id}
                                    className="cell auto"
                                    labelText={patch.path ? t('admin.documentations.change_document') : t('admin.documentations.add_document')}
                                    accept="application/pdf"
                                    onChange={this.updateDocument}/>

                        {
                            patch.path &&
                            <Button
                                id="delete-document-patch"
                                className="cell auto"
                                classNameButton="float-right alert"
                                labelText={t('admin.documentations.remove_document')}
                                onClick={this.deleteFile}
                            />
                        }
                    </div>
                </div>


                <div className="cell medium-2">
                    <div className="grid-x grid-margin-y grid-padding-y">
                        <Button id="submit-infos"
                                className="cell auto"
                                classNameButton="float-right"
                                onClick={this.updatePatch}
                                labelText={t('common.button.save')}
                                disabled={!editState}
                        />
                    </div>

                    <div className="grid-x">
                        <Button
                            id={ "delete-patch" + patch.id }
                            className="cell auto"
                            classNameButton="float-right alert"
                            labelText={t('common.button.delete')}
                            onClick={ this.delete }
                        />
                    </div>
                </div>
            </div>
        )
    }

}

DocumentationPatch.PropTypes = {
    patch: object.isRequired,
    delete: func.isRequired
}

export default translate(['sesile'])(DocumentationPatch)