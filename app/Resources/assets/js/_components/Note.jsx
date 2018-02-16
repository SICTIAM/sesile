import React, { Component } from 'react'

class Note extends Component {
    state = {
        note: {
            note: {
                title: '',
                subtitle: '',
                message: ''
            },
            alreadyOpen: true
        },
        isOpen: false
    }
    componentDidMount() {
        fetch(Routing.generate('sesile_user_noteapi_getlast'), {credentials: 'same-origin'})
        .then(response => response.json())
        .then(note => {if(note.note) this.setState({note: note, isOpen: !note.alreadyOpen})})
    }
    updateUserNote() {
        const { id } = this.state.note.note
        fetch(Routing.generate('sesile_user_noteapi_readed', {id}), {
            method: 'POST',
            headers: {
                'Accept': 'Applicaton/json',
                'Content-Type': 'Application/json'
            },
            credentials: 'same-origin'
        })
    }
    handleChangeOpen = () => {
        this.setState(prevState => {return {isOpen: !prevState.isOpen}})
        if (!this.state.note.alreadyOpen) {
            const { note } = this.state
            note['alreadyOpen'] = true
            this.setState({note})
            this.updateUserNote()
        }
    }
    render () {
        const { note, isOpen } = this.state
        return (
            <div>
            {
                isOpen &&
                    <div className="grid-x auto">
                        <div className="cell medium-12">
                            <div className="grid-y">
                                <div className="cell medium-6 grid-x">
                                    <div className="cell medium-12 actu">
                                        <div className="grid-x grid-padding-x">
                                            <div className="cell medium-12 align-center-middle text-center">
                                                <h1>{note.note.title}</h1>
                                            </div>
                                        </div>
                                        <div className="grid-x grid-padding-x">
                                            <div className="cell medium-12 align-center-middle text-center">
                                                <h2>{note.note.subtitle}</h2>
                                            </div>
                                        </div>

                                        <div className="grid-x grid-padding-x align-right align-bottom">
                                            <div className="cell medium-8 text-center">
                                                <div dangerouslySetInnerHTML={{__html: note.note.message}}/>
                                            </div>

                                            <div className="cell medium-2 text-right">
                                                <span className="fa fa-times note-ico"
                                                      onClick={this.handleChangeOpen}></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="cell medium-6">&nbsp;</div>
                        </div>
                    </div>
            }
            </div>
        )
    }

}

export default Note