import React, { Component } from 'react'
import { object, func } from 'prop-types'
import { translate } from 'react-i18next'
import Moment from 'moment'

import { AdminList, AdminPage, AdminContainer, AdminListRow } from "../_components/AdminUI"
import { Cell, GridX } from '../_components/UI'
import ButtonConfirmDelete from '../_components/ButtonConfirmDelete'
import { Input } from '../_components/Form'
import { basicNotification } from "../_components/Notifications"

import History from '../_utils/History'
import { DisplayLongText, handleErrors } from '../_utils/Utils'
import { escapedValue } from '../_utils/Search'

class Notes extends Component {
    static contextTypes = {
        t: func,
        _addNotification: func
    }
    state = {
        valueSearchByTitle: '', 
        filtredNotes: [],
        notes: [],
    }
    componentDidMount() {
        fetch(Routing.generate('sesile_user_noteapi_get'), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(notes => this.setState({notes, filtredNotes: notes}))
    }
    deleteNote = (id) => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_user_noteapi_remove', {id}), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(handleErrors)
        .then(response => response.json())
        .then((notes) => {
            this.setState({notes, filtredNotes: notes})
            _addNotification(basicNotification(
                'success',
                t('admin.notes.succes_delete')))
        })
        .catch(error => 
            _addNotification(basicNotification(
                'error',
                t('admin.notes.error_delete'))))
    }
    searchNoteByTitle = (key, valueSearchByTitle) => {
        this.setState({valueSearchByTitle})
        const regex = escapedValue(valueSearchByTitle, this.state.filtredNotes, this.state.notes)
        const filtredNotes = this.state.notes.filter(note => regex.test(note.title))
        this.setState({filtredNotes})
    }
    render () {
        const { t } = this.context
        const listNote = this.state.filtredNotes.map((note, key) => <RowNote key={note.id} note={note} deleteNote={this.deleteNote} />)
        return (
            <AdminPage
                title={t('admin.notes.title')}
                subtitle={t('admin.subtitle')}>
                <AdminContainer>
                    <div className="grid-x cell medium-12 align-center">
                        <Input
                            className="cell medium-6 align-center-middle"
                            labelText={t('admin.label.which', {context: 'female'})}
                            value={this.state.valueSearchByTitle}
                            onChange={this.searchNoteByTitle}
                            placeholder={t('admin.notes.search_by_title')}
                            type="text"/>
                    </div>
                    <AdminList
                        title={t('admin.notes.list_title')}
                        listLength={listNote.length}
                        labelButton={t('common.button.add')}
                        addLink="/admin/note/"
                        headTitles={[t('common.label.title'), t('common.label.subtitle'), t('common.label.date'), t('common.label.actions')]}
                        emptyListMessage={t('common.no_results', {name: t('admin.notes.name'), context: 'female'})}>
                            {listNote}
                    </AdminList>
                </AdminContainer>
            </AdminPage>
        )
    }
}

Notes.PropTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(Notes)

const RowNote = ({note, deleteNote}, {t}) => {
    return (
        <AdminListRow>
            <Cell className="medium-auto">
                <DisplayLongText text={note.title} />
            </Cell>
            <Cell className="medium-auto">
                <DisplayLongText text={note.subtitle} />
            </Cell>
            <Cell className="medium-auto">
                {Moment(note.created).format('LL')}
            </Cell>
            <Cell className="medium-auto">
                <GridX>
                    <Cell className="medium-auto">
                        <i  className="fa fa-pencil icon-action"
                            title={t('common.button.edit')} 
                            onClick={() => History.push(`/admin/note/${note.id}`)} >
                        </i>
                    </Cell>
                    <Cell className="medium-auto">
                        <ButtonConfirmDelete
                            id={note.id}
                            dataToggle={`delete-confirmation-note-${note.id}`}
                            onConfirm={deleteNote}
                            content={t('common.confirm_deletion_item')} />
                    </Cell>
                </GridX>
            </Cell>
        </AdminListRow>
    )
}

RowNote.propTypes = {
    note: object.isRequired
}

RowNote.contextTypes = {
    t: func
}