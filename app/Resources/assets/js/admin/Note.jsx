import React, { Component } from 'react'
import { object, func } from 'prop-types'
import { translate } from 'react-i18next'
import Validator from 'validatorjs'

import { basicNotification } from '../_components/Notifications'
import { Button, Form } from '../_components/Form'
import { GridX, Cell } from '../_components/UI'
import Editor from '../_components/Editor'
import { SimpleContent, AdminDetails } from '../_components/AdminUI'
import InputValidation from '../_components/InputValidation'

import History from '../_utils/History'
import { handleErrors } from '../_utils/Utils'

class Note extends Component {
    static contextTypes = {
        t: func,
        _addNotification: func
    }
    state = {
        editState: false,
        validator: new Validator(),
        note: {
            id: null,
            title: '',
            subtitle: ''
        }
    }
    validationRules = {
        title: 'required|string',
        subtitle: 'string'
    }
    componentDidMount() {
        const { noteId } = this.props.match.params
        noteId
            ? this.fetchNote(noteId)
            : this.setState(prevState => prevState.note.message = '')
    }
    fetchNote(id) {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_user_noteapi_getid', {id}), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(note => this.setState({note}))
            .catch(error => 
                _addNotification(basicNotification(
                    'error',
                    t('admin.notes.error_fetch'))))
    }
    handleClickSave = () => {
        const { note, validator } = this.state
        const fields = {
            title: note.title,
            subtitle: note.subtitle,
            message: note.message
        }
        if(this.formValidation(fields)) {
            this.props.match.params.noteId ? this.updateNote(fields) : this.addNote(fields)
        }
    }
    updateNote(fields) {
        const { t } = this.context
        const { note } = this.state
        fetch(Routing.generate('sesile_user_noteapi_update', {id: note.id}), {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(fields),
            credentials: 'same-origin'
        })
        .then(handleErrors)
        .then(response => response.json())
        .then(note => {
            History.push("/admin/notes")
            this.context._addNotification(basicNotification(
                'success',
                t('admin.notes.success_save')))
        })
        .catch(error => 
            this.context._addNotification(basicNotification(
                'error',
                t('admin.notes.error_save'))))
    }
    addNote(fields) {
        const { t } = this.context
        fetch(Routing.generate('sesile_user_noteapi_post'), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(fields) ,
            credentials: 'same-origin'
        })
        .then(handleErrors)
        .then(response => response.json())
        .then(note => {
            History.push('/admin/notes')
            this.context._addNotification(basicNotification(
                'success',
                t('admin.notes.success_save')))
        })
        .catch(error => 
            this.context._addNotification(basicNotification(
                'error',
                t('admin.notes.error_save'))))
    }
    handleChangeNote = (name, value) => {
        const { note } = this.state
        note[name] = value
        this.setState({note, editState: true})
    }
    formValidation = (fields) => {
        const validator = new Validator(
            fields, 
            {title: this.validationRules.title, subtitle: this.validationRules.subtitle})
        this.setState({validator})
        if(validator.passes()) return true
        else return false
    }
    render () {
        const { t } = this.context
        const { note, editState, validator } = this.state
        return (
            <Form onSubmit={this.handleClickSave}>
                <AdminDetails
                    title={t('admin.details.title', {context: 'female', name: t('admin.notes.name')})}
                    subtitle={t('admin.details.subtitle')} 
                    nom={t('admin.notes.name')} >
                    <SimpleContent>
                        <GridX className="grid-padding-x grid-padding-y">
                            <Cell className="medium-6">
                                <InputValidation    
                                    id="title"
                                    type="text"
                                    autoFocus={true}
                                    labelText={`${t('common.label.title')} *`}
                                    value={note.title}
                                    isValid={validator.passes() || true} 
                                    errorMessage={validator.errors.get('title')}
                                    onChange={this.handleChangeNote}
                                    validationRule={this.validationRules.title}
                                    placeholder={t('admin.notes.placeholder_title')}/>
                            </Cell>
                            <Cell className="medium-6">
                                <InputValidation    
                                    id="subtitle"
                                    type="text"
                                    labelText={t('common.label.subtitle')}
                                    value={note.subtitle}
                                    isValid={validator.passes() || true} 
                                    errorMessage={validator.errors.get('subtitle')}
                                    onChange={this.handleChangeNote}
                                    validationRule={this.validationRules.subtitle}
                                    placeholder={t('admin.notes.placeholder_subtitle')}/>
                            </Cell>
                            <Cell>
                                <Editor 
                                    id="message"
                                    label={t('admin.placeholder.message')}
                                    value={note.message}
                                    handleChange={this.handleChangeNote}/>
                            </Cell>
                            <Button 
                                disabled={!editState}
                                classNameButton="primary"
                                className="cell medium-12 text-right"
                                onClick={this.handleClickSave}
                                labelText=
                                    {this.props.match.params.noteId ? 
                                        t('common.button.edit_save') : 
                                        t('common.button.save')}/>
                        </GridX>
                    </SimpleContent>
                </AdminDetails>
            </Form>
        )
    }
}

Note.PropTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(Note)
