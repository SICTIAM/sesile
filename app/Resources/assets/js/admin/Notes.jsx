import React, { Component } from 'react'
import { object, func, string } from 'prop-types'
import { translate } from 'react-i18next'
import { Link } from 'react-router-dom'
import History from '../_utils/History'
import Moment from 'moment'

class Notes extends Component {

    static contextTypes = {
        t: func
    }

    constructor() {
        super()
        this.state = {
            notes: [],
        }
    }

    componentDidMount() {
        this.fetchNotes()
    }

    fetchNotes() {
        fetch(Routing.generate('sesile_user_noteapi_get'), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(notes => this.setState({notes}))
    }

    render () {

        const { notes } = this.state
        const { t } = this.context
        const listNotes = notes.map((note, key) => <NoteRow key={key} note={note} />)

        return (
            <div className="user-group">
                <h4 className="text-center text-bold">{t('admin.notes.complet_name')}</h4>
                <div className="grid-x align-center-middle">
                    <div className="cell medium-10 list-admin">
                    <div className="grid-x grid-padding-x panel">
                        <div className="cell medium-12 panel-heading">
                            <div className="grid-x">
                                <div className="cell medium-4">{t('admin.notes.note_title')}</div>
                                <div className="cell medium-4">{t('admin.notes.note_subtitle')}</div>
                                <div className="cell medium-4">{t('admin.notes.note_created')}</div>
                            </div>
                        </div>
                        {(notes.length > 0) ? listNotes :
                            <div className="cell medium-12 panel-body">
                                {t('common.no_results', {name: t('admin.notes.title')})}
                            </div>
                        }
                        <AddNoteRow />
                        </div>
                    </div>
                </div>
            </div>

        )
    }
}

Notes.PropTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(Notes)

const NoteRow = ({ note }) => {
    return (
        <div className="cell medium-12 panel-body grid-x row-admin" onClick={() => History.push(`/admin/note/${note.id}`)}>
            <div className="cell medium-4 text-uppercase">
                {note.title}
            </div>
            <div className="cell medium-4 text-uppercase">
                {note.subtitle}
            </div>
            <div className="cell medium-4 text-uppercase">
                { Moment(note.created).format('LLL') }
            </div>
        </div>
    )
}

NoteRow.propTypes = {
    note: object.isRequired
}

const AddNoteRow = ({}, {t}) => {

    return (
        <div className="cell medium-12 panel-body grid-x row-admin">
            <div className="cell medium-12">
                <Link to="/admin/note/" className="button primary">{t('common.button.add')}</Link>
            </div>
        </div>
    )
}

AddNoteRow.contextTypes = {
    t: func
}