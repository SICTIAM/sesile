import React, { Component } from 'react'
import { func, number, string } from 'prop-types'
import { translate } from 'react-i18next'

import { Button } from './Form'
import { GridX, Cell } from './UI'
import {DisplayLongText} from "../_utils/Utils";

class ButtonDropdown extends Component {
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
        const { t } = this.context
        return (
            <div id={`container-${this.props.dataToggle}`} className={this.props.className}>
                <button data-toggle={this.props.dataToggle} style={{cursor: 'pointer'}}>
                    <DisplayLongText
                        text={this.props.content.map(user => `${user._prenom} ${user._nom}`).join(' | ')}
                        maxSize={40}/>
                </button>
                <div
                    id={this.props.dataToggle}
                    data-position="bottom"
                    data-alignment="center"
                    className={`dropdown-pane ${this.state.isOpen} dropdown-confirm-delete text-center`}
                    data-close-on-click={true}
                    data-dropdown data-auto-focus={true}>
                    <GridX className="grid-margin-y">
                        <Cell className="medium-12 text-bold text-center">
                            <span>
                                {this.props.content.map((user, key) =>
                                    <ul key={key} style={{marginLeft:"0", marginBottom:"0"}}>
                                        {user._prenom} {user._nom}
                                    </ul>)}
                            </span>
                        </Cell>
                    </GridX>
                </div>
            </div>
        )
    }
}

ButtonDropdown.protoTypes = {
    id: number.isRequired,
    contenttext: string.isRequired,
    content: string.isRequired,
    onConfirm: func.isRequired,
    dataToggle: string,
    className: string
}

export default translate(['sesile'])(ButtonDropdown)