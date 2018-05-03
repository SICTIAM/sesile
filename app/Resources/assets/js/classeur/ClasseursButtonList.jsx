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
        return !this.props.classeurs.filter(classeur => !classeur[actionName]).length
    }
    render () {

        const { textRefus } = this.state
        const { classeurs, validClasseur, signClasseur, revertClasseur, removeClasseur, deleteClasseur, refuseClasseur, display, id, dropdownPosition, user } = this.props
        const { t } = this.context

        return (
            <div className="grid-x button-list align-middle text-center">
                <div className="cell auto">
                    <ButtonValid classeurs={ classeurs } valid={ validClasseur } enabled={this.actionEnabled('validable')} />
                </div>
                <div className="cell auto">
                    <div className={"btn-sign-" + id}>
                        {this.actionEnabled('signable_and_last_validant') ?
                            display === "list" ?
                                <a title={t('common.classeurs.button.sign_title')}
                                    className="fa fa-pencil" onClick={() => signClasseur(classeurs)} /> :
                                <div className="user-log">
                                    <button
                                        style={{border: 'none'}}
                                        data-toggle={id + 'sign'}
                                        title={t('common.classeurs.button.sign_title')}
                                        className="fa fa-pencil success button hollow"/>
                                    <div
                                        className="dropdown-pane"
                                        data-position={dropdownPosition}
                                        data-alignment="center"
                                        id={id + 'sign'}
                                        data-dropdown>
                                        {(user && user.userrole && user.userrole.length > 0) ?
                                            user.userrole.map(role => (
                                                <li key={role.id}>
                                                    <button onClick={() => signClasseur(classeurs, role.id)}
                                                            title={role.user_roles}
                                                            className="button secondary clear">
                                                        {role.user_roles}
                                                    </button>
                                                </li>)) :
                                            t('common.classeurs.button.no_roles')}
                                    </div>
                                </div> :
                            <i title={t('common.classeurs.button.sign_title')} className="fa fa-pencil disabled" />}
                    </div>
                </div>
                <div className="cell auto">
                    <ButtonRevert classeurs={ classeurs } revert={ revertClasseur } enabled={this.actionEnabled('retractable')} />
                </div>
                <div className="cell auto">
                    {
                        classeurs && !classeurs.filter(classeur => !classeur.refusable).length &&
                        <div className={"btn-refuser-" + id}>
                            {   <a data-toggle={id} title={t('common.classeurs.button.refus_title')}
                                     className="fa fa-minus-circle alert hollow"/>
                            }
                            <div className="dropdown-pane" data-position={dropdownPosition} data-alignment="right" id={id} data-dropdown data-auto-focus="true">
                            <Textarea id={id}
                                      name="text-refus"
                                      value={ textRefus }
                                      placeholder={t('common.classeurs.button.refus_text')}
                                      onChange={this.handleTextrefus}/>
                                <button onClick={() => refuseClasseur(classeurs, textRefus)} title={t('common.classeurs.button.refus_title')} className="alert button hollow" >{t('common.classeurs.button.refus_title')}</button>
                            </div>
                        </div>

                    }
                </div>
                <div className="cell auto">
                    {
                        classeurs && !classeurs.filter(classeur => !classeur.removable).length &&
                        <ButtonRemove classeurs={ classeurs } remove={ removeClasseur } display={display} />
                    }
                </div>
                <div className="cell auto">
                    {
                        classeurs && !classeurs.filter(classeur => !classeur.deletable).length &&
                        <ButtonDelete classeurs={ classeurs } deleteClasseur={ deleteClasseur } display={display} />
                    }
                </div>

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
            <a onClick={() => valid(classeurs)} title={ t('common.classeurs.button.valid_title') } className="fa fa-check success hollow"></a> :
            <i title={ t('common.classeurs.button.valid_title') } className="fa fa-check disabled hollow"></i>
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
            <a onClick={() => revert(classeurs)} title={ t('common.classeurs.button.revert_title') } className="fa fa-repeat warning hollow"></a> :
            <i title={ t('common.classeurs.button.revert_title') } className="fa fa-repeat disabled hollow"></i>
    )
}
ButtonRevert.contextTypes = { t: func }
ButtonRevert.propTypes = {
    classeurs: array,
    revert: func
}

const ButtonRemove = ({classeurs, remove, display}, {t}) => {
    return(<a onClick={() => remove(classeurs)} title={ t('common.classeurs.button.remove_title') } className="fa fa-times alert hollow"></a>
    )
}
ButtonRemove.contextTypes = { t: func }
ButtonRemove.propTypes = {
    classeurs: array,
    remove: func
}

const ButtonDelete = ({classeurs, deleteClasseur, display}, {t}) => {
    return(<a onClick={() => deleteClasseur(classeurs)}  title={ t('common.classeurs.button.delete_title') } className="fa fa-trash alert hollow"></a>
    )
}
ButtonDelete.contextTypes = { t: func }
ButtonDelete.propTypes = {
    classeurs: array,
    deleteClasseur: func
}