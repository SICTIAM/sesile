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
                                <div
                                    key={`etape ${key}`}
                                    className={`align-middle`}
                                    style={{
                                        marginBottom: '10px',
                                        width: '100%',
                                        minHeight: '5em',
                                        display: 'flex',
                                        boxShadow: 'rgba(34, 36, 38, 0.15) 0px 1px 2px 0px',
                                        borderRadius: '0.285714rem',
                                        border: '1px solid',
                                        padding: '0.5em',
                                        color: '#797c92'
                                    }}>
                                    <div className="text-center" style={{display: 'inline-block', width: '2.5rem'}}>
                                        <div style={{border:"solid 0.3em", fontSize: "1.125em",borderRadius: "2em", width: "2em", height: "2em"}}>
                                            {key + 1}
                                        </div>
                                    </div>
                                    <div
                                        className="align-right"
                                        style={{
                                            width: '65%',
                                            marginTop: '0'
                                        }}>
                                        <div className={`text-bold`}>
                                            {etapeGroupe.users && etapeGroupe.users.filter(user => user.id).map((user, userKey) =>
                                                <div key={"user" + user.id}
                                                     style={{display: 'inline-block', width: '100%'}}>
                                                    <div style={{display: 'inline-block', width: '89%'}}>
                                                        {user._prenom} {user._nom}
                                                    </div>
                                                </div>
                                            )}
                                            {etapeGroupe.user_packs && etapeGroupe.user_packs.filter(user_pack => user_pack.id).map((user_pack, userKey) =>
                                                <div key={"userpack" + user_pack.id}
                                                     style={{display: 'inline-block', width: '100%'}}>
                                                    <div style={{display: 'inline-block', width: '89%'}}>
                                                        {user_pack.nom}
                                                    </div>
                                                </div>
                                            )}
                                        </div>
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