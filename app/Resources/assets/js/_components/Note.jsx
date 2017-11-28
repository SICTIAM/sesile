import React, { Component } from 'react'

class Note extends Component {

    constructor() {
        super()
        this.state = {
            noteMaj: {
                open: false,
                note: {}
            },
            initialOpen: ''
        }
    }

    componentDidMount () {
        this.fetchNote()
    }

    fetchNote () {
        fetch(Routing.generate('sesile_user_noteapi_getlast'), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(notemaj => {
                this.setState({noteMaj: notemaj, initialOpen: notemaj.open})
            })
    }

    updateUserNote () {
        const { id } = this.state.noteMaj.note
        fetch(Routing.generate('sesile_user_noteapi_postusernote', {id}), {
            method: 'POST',
            headers: {
                'Accept': 'Applicaton/json',
                'Content-Type': 'Application/json'
            },
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(note => {
                this.setState(note)
            })
    }

    handleChangeOpen = () => {
        const { noteMaj } = this.state
        noteMaj['open'] = !noteMaj.open
        this.setState({noteMaj})
        if (this.state.initialOpen) {
            this.updateUserNote()
        }
    }

    render () {
        const { open, note } = this.state.noteMaj

        return (
            <div className={open ? "grid-x auto" : "grid-x medium-1"}>
                <div className="cell medium-12">

                    <div className="grid-y">
                        <div className="cell medium-6 grid-x">
                            {
                                note && (
                                open ?
                                    <div className="cell medium-12 actu">
                                        <div className="grid-x grid-padding-x">
                                            <div className="cell medium-12 align-center-middle text-center">
                                                <h1>{ note.title }</h1>
                                            </div>
                                        </div>
                                        <div className="grid-x grid-padding-x">
                                            <div className="cell medium-12 align-center-middle text-center">
                                                <h2>{ note.subtitle }</h2>
                                            </div>
                                        </div>

                                        <div className="grid-x grid-padding-x align-right align-bottom">
                                            <div className="cell medium-8 text-center">
                                                <div dangerouslySetInnerHTML={{ __html: note.message }} />
                                            </div>

                                            <div className="cell medium-2 text-right">
                                                <span className="fi-x note-ico" onClick={ this.handleChangeOpen }></span>
                                            </div>
                                        </div>
                                    </div>
                                    :
                                    <div className="cell medium-12 actu" onClick={ this.handleChangeOpen }>
                                        <div className="grid-x grid-padding-x align-right align-middle">
                                            <div className="cell medium-8 text-center">
                                                <h5>{ note.title }</h5>
                                            </div>
                                            <div className="cell medium-2 text-right">
                                                <span className="fi-plus note-ico"></span>
                                            </div>
                                        </div>
                                    </div>
                                )
                            }
                        </div>
                    </div>
                    <div className="cell medium-6">&nbsp;</div>
                </div>

            </div>
        )
    }

}

export default Note