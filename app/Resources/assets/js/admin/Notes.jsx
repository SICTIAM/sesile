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
        _addNotification: func,
        fetchUserNote: func
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
        this.context.fetchUserNote()
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
    searchNoteByTitle = (e) => {
        this.setState({valueSearchByTitle: e.target.value})
        const regex = escapedValue(e.target.value, this.state.filtredNotes, this.state.notes)
        const filtredNotes = this.state.notes.filter(note => regex.test(note.title))
        this.setState({filtredNotes})
    }
    onClickButton = (e) => {
        e.preventDefault()
        e.stopPropagation()
    }
    render () {
        const {t} = this.context
        const listNote = this.state.filtredNotes.map((note, key) => <RowNote key={note.id} note={note}
                                                                             onClick={this.onClickButton}
                                                                             deleteNote={this.deleteNote}/>)
        return (
            <AdminPage>
                <div className="cell medium-12 text-center">
                    <h2>{t('admin.notes.name_plural')}</h2>
                </div>
                <AdminContainer>
                    <div className="grid-x grid-padding-x panel align-center-middle"
                         style={{width: "74em", marginTop: "1em"}}>
                        <div className="cell medium-12 grid-x panel align-center-middle"
                             style={{
                                 display: "flex",
                                 marginBottom: "0em",
                                 marginTop: "10px",
                                 padding: "10px",
                                 width: "50%"
                             }}>
                            <input
                                value={this.state.valueSearchByTitle}
                                style={{margin:"0"}}
                                onChange={(e) => this.searchNoteByTitle(e)}
                                placeholder={t('admin.notes.search_by_title')}
                                type="text"/>
                        </div>

                        <div className="cell medium-12 text-right" style={{marginTop: "10px"}}>
                            <button
                                className="button hollow"
                                onClick={() => History.push(`/admin/note/`)}
                                style={{
                                    border: "1px solid rgb(204, 0, 102)",
                                    color: "rgb(204, 0, 102)"
                                }}>
                                {t('common.button.add')}
                            </button>
                        </div>
                        <table style={{margin:"10px", borderRadius:"6px", width:"98%"}}>
                            <thead>
                            <tr style={{backgroundColor:"#CC0066", color:"white"}}>
                                <td width="300px" className="text-bold">{ t('common.label.title') }</td>
                                <td width="300px" className="text-bold">{ t('common.label.subtitle') }</td>
                                <td width="130px" className="text-bold">{ t('common.label.date') }</td>
                                <td width="20px" className="text-bold">{ t('common.label.actions') }</td>
                            </tr>
                            </thead>
                            <tbody>
                            {listNote.length > 0 ?
                                listNote :
                                <tr>
                                    <td>
                                        <span style={{textAlign:"center"}}>{t('common.no_results_female', {name: t('admin.notes.name')})}</span>
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>}
                            </tbody>
                        </table>
                    </div>
                </AdminContainer>
            </AdminPage>
        )
    }
}

Notes.PropTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(Notes)

const RowNote = ({note, deleteNote, onClick}, {t}) => {
    return (
        <tr onClick={() => History.push(`/admin/note/${note.id}`)} style={{cursor:"Pointer"}}>
            <td>
                <DisplayLongText text={note.title} />
            </td>
            <td>
                <DisplayLongText text={note.subtitle} />
            </td>
            <td>
                {Moment(note.created).format('LL')}
            </td>
            <td onClick={(e) => onClick(e)}>
                <ButtonConfirmDelete
                    id={note.id}
                    dataToggle={`delete-confirmation-note-${note.id}`}
                    onConfirm={deleteNote}
                    content={t('common.confirm_deletion_item')} />
            </td>
        </tr>
    )
}

RowNote.propTypes = {
    note: object.isRequired
}

RowNote.contextTypes = {
    t: func
}