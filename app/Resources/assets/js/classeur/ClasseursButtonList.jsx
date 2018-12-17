import React, { Component } from 'react'
import { object, array, func, number, string }from 'prop-types'
import { translate } from 'react-i18next'

import {Textarea} from '../_components/Form'

class ClasseursButtonList extends Component {

    static contextTypes = {
        t: func
    }

    static defaultProps = {
        display: "list",
        dropdownPosition: "bottom"
    }

    state = {
        textRefus: ''
    }

    componentDidMount() {
        $('.btn-sign-' + this.props.id).foundation()
        $('.btn-refuser-' + this.props.id).foundation()
    }

    handleTextrefus = (name, value) => {
        this.setState({textRefus: value})
    }
    actionEnabled = (actionName) => {
        return !this.props.classeurs.filter(classeur => !classeur[actionName]).length && this.props.classeurs.length > 0
    }

    render() {

        const {textRefus} = this.state
        const {classeurs, validClasseur, signClasseur, revertClasseur, removeClasseur, deleteClasseur, refuseClasseur, display, id, dropdownPosition, user} = this.props
        const {t} = this.context
        return (
            <div style={this.props.style} className="grid-x button-list align-middle text-center">
                <div className="cell medium-auto">
                    <ButtonValid classeurs={classeurs} valid={validClasseur}
                                 enabled={this.actionEnabled('validable') && !this.props.signatureInProgress}/>
                </div>
                <div className="cell medium-auto">
                    <div className={"btn-sign-" + id}>
                        {this.actionEnabled('signable_and_last_validant') && !this.props.signatureInProgress ?
                            display === "list" ?
                                <a
                                    className="fa fa-edit success hollow"
                                    onClick={(e) => signClasseur(e, classeurs)}/> :
                                <div className="user-log">
                                    <div className="tooltip">
                                        <button
                                            style={{border: 'none'}}
                                            data-toggle={id + 'sign'}
                                            className="fa fa-edit success button hollow"/>
                                        <span className="tooltiptext">{t('common.classeurs.button.sign_tooltip')}</span>
                                    </div>
                                    <div
                                        className="dropdown-pane text-left"
                                        data-position={dropdownPosition}
                                        data-alignment="center"
                                        id={id + 'sign'}
                                        data-close-on-click={true}
                                        data-dropdown data-auto-focus={true}
                                        style={{padding: '5px'}}>
                                        {(user && user.userrole && user.userrole.length > 0) ?
                                            user.userrole.map(role => (
                                                <li key={role.id}>
                                                    <button
                                                        style={{padding: '5px'}}
                                                        onClick={(e) => signClasseur(e, classeurs, role.id)}
                                                        title={role.user_roles}
                                                        className="button secondary clear">
                                                        {role.user_roles}
                                                    </button>
                                                </li>)) :
                                            t('common.classeurs.button.no_roles')}
                                    </div>
                                </div> :
                            <i title={t('common.classeurs.button.sign_title')} className="fa fa-edit disabled hollow"/>}
                    </div>
                </div>
                <div className="cell medium-auto">
                    <ButtonRevert classeurs={classeurs} revert={revertClasseur}
                                  enabled={this.actionEnabled('retractable') && !this.props.signatureInProgress}/>
                </div>
                <div className="cell medium-auto">
                    {this.actionEnabled('refusable') && !this.props.signatureInProgress ?
                        <div className={"btn-refuser-" + id}>
                            <a
                                data-toggle={`toggle-refus-${id}`} title={t('common.classeurs.button.refus_title')}
                                className="fa fa-minus-circle alert hollow"/>
                            <div
                                id={`toggle-refus-${id}`}
                                className="dropdown-pane"
                                data-position={dropdownPosition}
                                data-alignment="right"
                                style={{width: '370px'}}
                                data-close-on-click={true}
                                data-dropdown data-auto-focus={true}>
                                <Textarea
                                    id={`textarea-${id}`}
                                    name="text-refus"
                                    value={textRefus}
                                    style={{height: 200}}
                                    placeholder={t('common.classeurs.button.refus_text')}
                                    onChange={this.handleTextrefus}/>
                                <button
                                    onClick={(e) => refuseClasseur(e, classeurs, textRefus)}
                                    title={t('common.classeurs.button.refus_title')}
                                    className="alert button hollow">
                                    {t('common.classeurs.button.refus_title')}
                                </button>
                            </div>
                        </div> :
                        <i title={t('common.classeurs.button.refus_title')} className="fa fa-minus-circle disabled"/>}
                </div>
                <div className="cell medium-auto">
                    <ButtonRemove
                        classeurs={classeurs}
                        remove={removeClasseur}
                        enabled={this.actionEnabled('removable') && !this.props.signatureInProgress}/>
                </div>
                <div className="cell medium-auto">
                    <ButtonDelete
                        classeurs={classeurs}
                        deleteClasseur={deleteClasseur}
                        enabled={this.actionEnabled('deletable') && !this.props.signatureInProgress}/>
                </div>
                {this.props.check !== undefined &&
                <div className="cell medium-auto">
                    <input id={this.props.id} value={this.props.checked} onClick={(e) => this.props.check(e)}
                           type="checkbox"/>
                </div>}
            </div>
        )
    }
}

ClasseursButtonList.PropTypes = {
    classeurs: array,
    validClasseur: func,
    revertClasseur: func,
    refuseClasseur: func,
    removeClasseur: func,
    deleteClasseur: func,
    signClasseur: func,
    display: string,
    id: string.isRequired,
    dropdownPosition: string
}

export default translate(['sesile'])(ClasseursButtonList)

const ButtonValid = ({classeurs, valid, enabled}, {t}) => {
    return (
        enabled ?
            <div className="tooltip">
                <a
                    onClick={(e) => valid(e, classeurs)}
                    className="fa fa-check success hollow"/>
                <span className="tooltiptext">{t('common.classeurs.button.valid_tooltip')}</span>
            </div> :
            <i title={t('common.classeurs.button.valid_title')} className="fa fa-check disabled hollow"/>
    )
}
ButtonValid.contextTypes = { t: func }
ButtonValid.propTypes = {
    classeurs: array,
    valid: func
}

const ButtonRevert = ({classeurs, revert, enabled}, {t}) => {
    return (
        enabled ?
            <div className="tooltip">
                <a
                    onClick={(e) => revert(e, classeurs)}
                    className="fa fa-repeat warning hollow"/>
                <span className="tooltiptext">{t('common.classeurs.button.revert_tooltip')}</span>
            </div> :
            <i title={t('common.classeurs.button.revert_title')} className="fa fa-repeat disabled hollow"/>
    )
}
ButtonRevert.contextTypes = { t: func }
ButtonRevert.propTypes = {
    classeurs: array,
    revert: func
}

const ButtonRemove = ({classeurs, remove, enabled}, {t}) => {
    return (
        enabled ?
            <div className="tooltip">
                <a
                    onClick={(e) => remove(e, classeurs)}
                    className="fa fa-times alert hollow"/>
                <span className="tooltiptext">{t('common.classeurs.button.remove_tooltip')}</span>
            </div> :
            <i title={t('common.classeurs.button.remove_title')} className="fa fa-times disabled hollow"/>
    )
}
ButtonRemove.contextTypes = { t: func }
ButtonRemove.propTypes = {
    classeurs: array,
    remove: func
}

const ButtonDelete = ({classeurs, deleteClasseur, enabled}, {t}) => {
    return (
        enabled ?
            <div className="tooltip">
                <a
                    onClick={(e) => deleteClasseur(e, classeurs)}
                    className="fa fa-trash alert hollow"/>
                <span className="tooltiptext">{t('common.classeurs.button.delete_tooltip')}</span>
            </div> :
            <i title={t('common.classeurs.button.delete_title')} className="fa fa-trash disabled hollow"/>
    )
}
ButtonDelete.contextTypes = { t: func }
ButtonDelete.propTypes = {
    classeurs: array,
    deleteClasseur: func
}