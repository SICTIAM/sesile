import React, { Component } from 'react'

class Note extends Component {
    static defaultProps = {
        noteObject: {
            note: {
                title: '',
                subtitle: '',
                message: ''
            },
            alreadyOpen: true
        }
    }
    state = {
        isOpen: false
    }
    componentDidMount() {
        this.props.fetchUserNote()
    }
    componentWillReceiveProps(nexProps) {
        if(nexProps.noteObject.note) this.setState({isOpen: !nexProps.noteObject.alreadyOpen})
    }
    updateUserNote() {
        const { id } = this.props.noteObject.note
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
        if (!this.props.noteObject.alreadyOpen) {
            this.props.handleChange()
            this.updateUserNote()
        }
    }
    render () {
        const { noteObject } = this.props
        return (
            <div>
            {
                this.state.isOpen &&
                    <div className="grid-x auto">
                        <div className="cell medium-12">
                            <div className="grid-y">
                                <div className="cell medium-6 grid-x">
                                    <div className="cell medium-12 actu">
                                        <div className="grid-x grid-padding-x">
                                            <div className="cell medium-12 align-center-middle text-center">
                                                <h1>{noteObject.note.title}</h1>
                                            </div>
                                        </div>
                                        <div className="grid-x grid-padding-x">
                                            <div className="cell medium-12 align-center-middle text-center">
                                                <h2>{noteObject.note.subtitle}</h2>
                                            </div>
                                        </div>

                                        <div className="grid-x grid-padding-x align-right align-bottom">
                                            <div className="cell medium-8 text-center">
                                                <div dangerouslySetInnerHTML={{__html: noteObject.note.message}}/>
                                            </div>

                                            <div className="cell medium-2 text-right">
                                                <button className="fa fa-times note-ico"
                                                      onClick={() => this.handleChangeOpen()}/>
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