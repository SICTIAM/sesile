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

class DocumentationHelp extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props)
        this.state = {
            help: {},
            editState: false
        }
    }

    componentDidMount() {
        this.state.help = this.props.help
    }

    componentWillReceiveProps(nextProps) {
        if (this.props.help !== nextProps.help) {
            this.setState({help: nextProps.help})
        }
    }

    validationRules = {
        id: 'required',
        description: 'required',
        date: 'required'
    }

    updateDocument = (file) => {
        if (file) {
            const data = new FormData()
            data.append('path', file)
            fetch(Routing.generate('sesile_main_documentationapi_uploadaidedocument', {id: this.state.help.id}), {
                credentials: 'same-origin',
                method: 'POST',
                body: data
            })
                .then(handleErrors)
                .then(response => response.json())
                .then(help => {
                    this.context._addNotification(basicNotification(
                        'success',
                        this.context.t('admin.documentations.success_upload')))
                    this.setState({help})}
                )
                .catch(error => this.context._addNotification(basicNotification(
                   'error',
                   this.context.t('admin.documentations.error.upload_document', {errorCode: error.status}),
                   error.statusText)))
        }
    }

    handleChange = (name, value) => {
        const { help } = this.state
        help[name] = value
        this.setState({help})
        this.formIsValid(help)
    }

    handleChangeDate = (date) => {
        const { help } = this.state
        help['date'] = date
        this.setState({help})
        this.formIsValid(help)
    }

    formIsValid = (fields) => {
        const validation = new Validator(fields, this.validationRules)
        this.setState({editState: validation.passes()})

        return validation.passes()
    }

    updateHelp = () => {
        const { id, description, date } = this.state.help

        if(this.formIsValid(this.state.help)) {

            fetch(Routing.generate('sesile_main_documentationapi_updateaide', {id}), {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    description: description,
                    date: Moment(date).format('YYYY-MM-DD HH:mm')
                }),
                credentials: 'same-origin'
            })
            .then(handleErrors)
            .then(response => response.json())
            .then(help => {
                this.context._addNotification(basicNotification(
                    'success',
                    this.context.t('admin.documentations.success_edit')))
                this.setState(help)
            })
            .catch(error => this.context._addNotification(basicNotification(
                'error',
                this.context.t('admin.documentations.error.fail_edit', {errorCode: error.status}),
                error.statusText)))
        }
    }

    deleteFile = () => {
        const { t, _addNotification } = this.context
        const { help } = this.state
        const id = help.id
        fetch(Routing.generate('sesile_main_documentationapi_deleteaidedocument', {id}), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then(help => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.success.delete', {name: t('admin.documentations.help')})
                ))
                this.setState({help})
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.documentations.error.delete_document', {errorCode: error.status}),
                error.statusText)))
    }

    delete = () => {
        this.props.delete(this.props.help.id)
    }

    render() {

        const { help, editState } = this.state
        const { t } = this.context
        const { i18nextLng } = window.localStorage

        return (
            <div className="grid-x grid-padding-x grid-padding-y">
                <InputValidation    id="description"
                                    type="text"
                                    className={"medium-3 cell"}
                                    labelText={t('common.label.description')}
                                    value={ help.description }
                                    onChange={this.handleChange}
                                    validationRule={this.validationRules.description}
                                    placeholder={t('admin.documentations.placeholder_description')}/>

                <InputDatePicker
                    label={ t('admin.documentations.label_date') }
                    date={ Moment(help.date) }
                    onChange={ this.handleChangeDate }
                    locale={ i18nextLng }
                    className="medium-2 cell"
                    />

                <div className="cell medium-5">
                    <div className="grid-x">
                        {
                            help.path &&
                            <div className="cell auto text-center">
                                <Link to={Routing.generate('download_aide', {id: help.id})} className="button primary" target="_blank">{t('common.help_board.view_button')}</Link>
                            </div>
                        }
                    </div>
                    <div className="grid-x grid-margin-y grid-padding-y">
                        <InputFile  id={"help-image" + help.id}
                                    className="cell auto"
                                    labelText={help.path ? t('admin.documentations.change_document') : t('admin.documentations.add_document')}
                                    accept="application/pdf"
                                    onChange={this.updateDocument}/>

                        {
                            help.path &&
                            <Button
                                id="delete-document-aide"
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
                                onClick={this.updateHelp}
                                labelText={t('common.button.save')}
                                disabled={!editState}
                        />
                    </div>

                    <div className="grid-x">
                        <Button
                            id={ "delete-aide" + help.id }
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

DocumentationHelp.PropTypes = {
    help: object.isRequired,
    delete: func.isRequired
}

export default translate(['sesile'])(DocumentationHelp)