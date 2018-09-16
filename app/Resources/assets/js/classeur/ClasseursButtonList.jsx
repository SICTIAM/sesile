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
    render () {

        const { textRefus } = this.state
        const { classeurs, validClasseur, signClasseur, revertClasseur, removeClasseur, deleteClasseur, refuseClasseur, display, id, dropdownPosition, user } = this.props
        const { t } = this.context
        console.log(this.props.checked)
        return (
            <div style={this.props.style} className="grid-x button-list align-middle text-center">
                <div className="cell medium-3">
                    <ButtonValid classeurs={ classeurs } valid={ validClasseur } enabled={this.actionEnabled('validable')} />
                </div>
                <div className="cell medium-3">
                    <div className={"btn-sign-" + id}>
                        {this.actionEnabled('signable_and_last_validant') ?
                            display === "list" ?
                                <a
                                    title={t('common.classeurs.button.sign_title')}
                                    className="fa fa-edit success hollow" onClick={() => signClasseur(classeurs)} /> :
                                <div className="user-log">
                                    <button
                                        style={{border: 'none'}}
                                        data-toggle={id + 'sign'}
                                        title={t('common.classeurs.button.sign_title')}
                                        className="fa fa-edit success button hollow"/>
                                    <div
                                        className="dropdown-pane text-left"
                                        data-position={dropdownPosition}
                                        data-alignment="center"
                                        id={id + 'sign'}
                                        data-dropdown
                                        style={{padding: '5px'}}>
                                        {(user && user.userrole && user.userrole.length > 0) ?
                                            user.userrole.map(role => (
                                                <li key={role.id}>
                                                    <button
                                                        style={{padding: '5px'}}
                                                        onClick={() => signClasseur(classeurs, role.id)}
                                                        title={role.user_roles}
                                                        className="button secondary clear">
                                                        {role.user_roles}
                                                    </button>
                                                </li>)) :
                                            t('common.classeurs.button.no_roles')}
                                    </div>
                                </div> :
                            <i title={t('common.classeurs.button.sign_title')} className="fa fa-edit disabled hollow" />}
                    </div>
                </div>
                <div className="cell medium-3">
                    <ButtonRevert classeurs={ classeurs } revert={ revertClasseur } enabled={this.actionEnabled('retractable')} />
                </div>
                {this.props.check !== undefined &&
                    <div className="cell medium-3" style={{marginTop: '5px'}}>
                        <input id={this.props.id} value={this.props.checked} onClick={(e) => this.props.check(e)} type="checkbox" />
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
    return(
        enabled ?
            <a
                onClick={() => valid(classeurs)}
                title={t('common.classeurs.button.valid_title')}
                className="fa fa-check success hollow"/> :
            <i title={t('common.classeurs.button.valid_title')} className="fa fa-check disabled hollow"/>
    )
}
ButtonValid.contextTypes = { t: func }
ButtonValid.propTypes = {
    classeurs: array,
    valid: func
}

const ButtonRevert = ({classeurs, revert, enabled}, {t}) => {
    return(
        enabled ?
            <a
                onClick={() => revert(classeurs)}
                title={ t('common.classeurs.button.revert_title') }
                className="fa fa-repeat warning hollow"/> :
            <i title={ t('common.classeurs.button.revert_title') } className="fa fa-repeat disabled hollow"/>
    )
}
ButtonRevert.contextTypes = { t: func }
ButtonRevert.propTypes = {
    classeurs: array,
    revert: func
}

const ButtonRemove = ({classeurs, remove, enabled}, {t}) => {
    return(
        enabled ?
            <a
                onClick={() => remove(classeurs)}
                title={t('common.classeurs.button.remove_title')}
                className="fa fa-times alert hollow"/> :
            <i title={t('common.classeurs.button.remove_title')} className="fa fa-times disabled hollow"/>
    )
}
ButtonRemove.contextTypes = { t: func }
ButtonRemove.propTypes = {
    classeurs: array,
    remove: func
}

const ButtonDelete = ({classeurs, deleteClasseur, enabled}, {t}) => {
    return(
        enabled ?
            <a
                onClick={() => deleteClasseur(classeurs)}
                title={t('common.classeurs.button.delete_title')}
                className="fa fa-trash alert hollow"/> :
            <i title={t('common.classeurs.button.delete_title')} className="fa fa-trash disabled hollow"/>
    )
}
ButtonDelete.contextTypes = { t: func }
ButtonDelete.propTypes = {
    classeurs: array,
    deleteClasseur: func
}