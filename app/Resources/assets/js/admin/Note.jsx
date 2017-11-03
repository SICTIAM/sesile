import React, { Component } from 'react'
import { object, func, string } from 'prop-types'
import { translate } from 'react-i18next'
import History from '../_utils/History'
import { handleErrors } from '../_utils/Utils'
import { basicNotification } from '../_components/Notifications'
import { Button, Input } from '../_components/Form'
import Editor from '../_components/Editor'

class Note extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor() {
        super()
        this.state = {
            note: {
                title: '',
                subtitle: ''
            }
        }
    }

    componentDidMount() {
        const { noteId } = this.props.match.params
        noteId && this.fetchNote(noteId)
    }

    fetchNote(id) {
        const { t } = this.context
        fetch(Routing.generate('sesile_user_noteapi_getid', {id}), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(note => this.setState({note}))
            .catch(error => this.context._addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {name:t('admin.notes.title'), errorCode: error.status}),
                error.statusText)))
    }

    handleSaveNote = () => {
        const { noteId } = this.props.match.params
        noteId ? this.updateNote() : this.addNewNote()
    }

    updateNote = () => {
        const { t } = this.context
        const { note } = this.state
        fetch(Routing.generate('sesile_user_noteapi_update', {id: note.id}), {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(({
                title: note.title,
                subtitle: note.subtitle,
                message: note.message
            })),
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then(note => {
                this.setState({note})
                this.context._addNotification(basicNotification(
                    'success',
                    t('admin.success.update', {name:t('admin.notes.title')})))
            })
            .catch(error => this.context._addNotification(basicNotification(
                'error',
                t('admin.error.not_updatable', {name:t('admin.notes.title'), errorCode: error.status}),
                error.statusText)))
    }

    addNewNote = () => {
        const { t } = this.context
        const { note } = this.state
        fetch(Routing.generate('sesile_user_noteapi_post'), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(({
                title: note.title,
                subtitle: note.subtitle,
                message: note.message

            })) ,
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then(note => {
                this.context._addNotification(basicNotification(
                    'success',
                    t('admin.success.add', {name:t('admin.notes.title')})))
                History.push(`/admin/note/${note.id}`)
            })
            .catch(error => this.context._addNotification(basicNotification(
                'error',
                t('admin.error.not_addable', {name:t('admin.notes.title'), errorCode: error.status}),
                error.statusText)))
    }

    handleDeleteNote = () => {
        const { t } = this.context
        const { noteId } = this.props.match.params
        fetch(Routing.generate('sesile_user_noteapi_remove', {id: noteId}), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(() => {
                this.context._addNotification(basicNotification(
                    'success',
                    t('admin.success.delete', {name:t('admin.notes.title')})
                ))
                History.push('/admin/notes')
            })
            .catch(error => this.context._addNotification(basicNotification(
                'error',
                t('admin.error.not_removable', {name:t('admin.notes.title'), errorCode: error.status}),
                error.statusText)))
    }

    handleChangeNote = (name, value) => {
        const { note } = this.state
        note[name] = value
        this.setState({note})
    }

    render () {

        const { note } = this.state
        const { t } = this.context
        const { noteId } = this.props.match.params

        return (

            <div className="grid-x">
                <div className="admin-details medium-12 cell">
                    <div className="grid-x admin-head-details">
                        { t('admin.notes.complet_name') }
                        <i className="fi-pencil small medium-6 cell"></i>
                    </div>
                    <div className="admin-content-details">

                        <div className="grid-x align-center-middle">
                            <Input id="title"
                                   className="cell medium-11"
                                   placeholder={ t('admin.notes.note_title') }
                                   labelText={ t('admin.notes.note_title') }
                                   value={ note.title }
                                   onChange={ this.handleChangeNote }
                            />
                        </div>
                        <div className="grid-x align-center-middle">
                            <Input id="subtitle"
                                   className="cell medium-11"
                                   placeholder={ t('admin.notes.note_subtitle') }
                                   labelText={ t('admin.notes.note_subtitle') }
                                   value={ note.subtitle }
                                   onChange={ this.handleChangeNote }
                            />
                        </div>
                        <div className="grid-x align-center-middle">
                            <Editor id="message"
                                    label={t('admin.placeholder.message')}
                                    className="cell medium-11"
                                    value={ note.message }
                                    handleChange={ this.handleChangeNote }
                            />
                        </div>
                        <div className="grid-x align-center-middle grid-margin-y grid-padding-y">
                            <Button id="delete-note"
                                    className="cell medium-6 text-left"
                                    classNameButton="alert"
                                    onClick={ this.handleDeleteNote }
                                    disabled={ !noteId }
                                    labelText={t('common.button.delete')}
                            />
                            <Button id="add-note"
                                    className="cell medium-5 text-right"
                                    classNameButton=""
                                    onClick={ this.handleSaveNote }
                                    disabled={ !note.title.length }
                                    labelText={t('common.button.save')}
                            />
                        </div>

                    </div>
                </div>
            </div>

        )
    }
}

Note.PropTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(Note)
