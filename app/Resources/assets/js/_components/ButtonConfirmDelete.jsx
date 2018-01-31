import React, { Component } from 'react'
import { func, number, string } from 'prop-types'
import { translate } from 'react-i18next'

import { Button } from './Form'
import { GridX, Cell } from './UI'

class ButtonConfirmDelete extends Component {
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
                <button data-toggle={this.props.dataToggle}>
                    <i className="fi-trash medium icon-action" title={t('common.button.delete')}></i>
                </button>
                <div 
                    id={this.props.dataToggle} 
                    data-position="bottom" 
                    data-alignment="center" 
                    className={`dropdown-pane ${this.state.isOpen} dropdown-confirm-delete`} 
                    data-dropdown 
                    data-auto-focus="true">
                    <GridX className="grid-margin-y">
                        <Cell className="medium-12 text-bold text-center">
                            <span>{this.props.content}</span>
                        </Cell>
                        <Cell>
                            <GridX>
                                <Cell className="medium-6">
                                    <button className="button" onClick={e => $(`#${this.props.dataToggle}`).foundation('close')}>
                                        {t('common.button.cancel')}
                                    </button>
                                </Cell>
                                <Cell className="medium-6 text-right">
                                    <button className="button alert" onClick={() => this.onConfirm()} >
                                        {t('common.button.confirm')}
                                    </button>
                                </Cell>
                            </GridX>
                        </Cell>
                    </GridX>
                </div>
            </div>
        )
    }
}

ButtonConfirmDelete.protoTypes = {
    id: number.isRequired,
    content: string.isRequired,
    onConfirm: func.isRequired,
    dataToggle: string,
    className: string
}

export default translate(['sesile'])(ButtonConfirmDelete)