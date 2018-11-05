import React, { Component } from 'react'
import { func, number, string } from 'prop-types'
import { translate } from 'react-i18next'

import { Button } from './Form'
import { GridX, Cell } from './UI'
import {DisplayLongText} from "../_utils/Utils";

class ButtonPopup extends Component {
    static contextTypes = {
        t: func
    }
    static defaultProps = {
        id: null,
        content: '',
        onConfirm: null,
    }
    state = {
        isOpen: ''
    }
    componentDidMount = () => $(`#container-${this.props.dataToggle}`).foundation()
    componentWillUnmount = () => $(`#${this.props.dataToggle}`).foundation('_destroy')

    onConfirm() {
        this.props.onConfirm(this.props.id)
        $(`#${this.props.dataToggle}`).foundation('close')
    }

    render() {
        const {t} = this.context
        return (
            <div id={`container-${this.props.dataToggle}`} className={this.props.className}>
                <button data-toggle={this.props.dataToggle}>
                    <DisplayLongText
                        text={this.props.content.map(etapeGroupe =>
                            etapeGroupe.users.map(user =>
                                `${user._prenom} ${user._nom}`).join(' | ')).join(' | ')}
                        maxSize={70}/>
                </button>
                <div
                    id={this.props.dataToggle}
                    data-position="bottom"
                    data-alignment="center"
                    className={`dropdown-pane ${this.state.isOpen} dropdown-confirm-delete text-center`}
                    data-close-on-click={true}
                    data-dropdown data-auto-focus={true}
                    style={{marginBottom: "0"}}>
                    <GridX className="grid-margin-y">
                        <Cell className="medium-12 text-bold text-center">
                                {this.props.content.map((etapeGroupe, key) =>
                                    <div key={`step-${etapeGroupe.id}`} style={{marginLeft: "0", fontSize: "0.8em"}}>
                                        <div className="text-left">{`Etape ${key + 1} :`}</div>
                                        <div className="panel" style={{fontWeight:"500", marginBottom:"1em"}}>
                                            {etapeGroupe.users.map(user =>
                                                <div key={`user-${user.id}`}>
                                                    <span>{`${user._prenom} ${user._nom}`}</span>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                )}
                        </Cell>
                    </GridX>
                </div>
            </div>
        )
    }
}

ButtonPopup.protoTypes = {
    id: number.isRequired,
    content: string.isRequired,
    onConfirm: func.isRequired,
    dataToggle: string,
    className: string
}

export default translate(['sesile'])(ButtonPopup)