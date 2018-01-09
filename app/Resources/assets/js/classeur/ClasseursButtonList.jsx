import React, { Component } from 'react'
import { object, array, func, number, oneOfType }from 'prop-types'
import { translate } from 'react-i18next'

class ClasseursButtonList extends Component {

    static contextTypes = {
        t: func
    }

    render () {

        const { classeur, classeurs, validClasseur } = this.props

        return (

            <div className="grid-x">
                {
                    classeur && classeur.validable &&
                    <ButtonValid classeurs={ [classeur] } valid={ validClasseur } />
                }
                {
                    classeurs && !classeurs.filter(classeur => !classeur.validable).length &&
                    <ButtonValid classeurs={ classeurs } valid={ validClasseur } />
                }

                {
                    (
                        classeur && classeur.signable_and_last_validant
                        || classeurs && !classeurs.filter(classeur => !classeur.signable_and_last_validant).length
                    ) &&
                    <ButtonSign/>
                }


                {
                    (
                        classeur && classeur.retractable
                        || classeurs && !classeurs.filter(classeur => !classeur.retractable).length
                    ) &&
                    <ButtonRevert/>
                }


                {
                    (
                        classeur && classeur.status === 3
                        || classeurs && !classeurs.filter(classeur => classeur.status !== 3).length > 0
                    ) &&
                    <ButtonRefus/>
                }

                {
                    <ButtonComment/>
                }


            </div>
        )
    }
}

ClasseursButtonList.PropTypes = {
    classeur: object,
    classeurs: array,
    validClasseur: func
}

export default translate(['sesile'])(ClasseursButtonList)

const ButtonValid = ({classeurs, valid}, {t}) => {

    return(
        <div className="cell auto"><a onClick={() => valid(classeurs)} title={ t('common.classeurs.button.valid_title') } className="btn-valid"></a></div>
    )
}
ButtonValid.contextTypes = { t: func }
ButtonValid.propTypes = {
    classeurs: array,
    valid: func
}

const ButtonSign = ({}, {t}) => {
    return(
        <div className="cell auto"><a href="#" title={ t('common.classeurs.button.sign_title') } className="btn-sign"></a></div>
    )
}
ButtonSign.contextTypes = { t: func }

const ButtonRevert = ({}, {t}) => {
    return(
        <div className="cell auto"><a href="#" title={ t('common.classeurs.button.revert_title') } className="btn-revert"></a></div>
    )
}
ButtonRevert.contextTypes = { t: func }

const ButtonRefus = ({}, {t}) => {
    return(
        <div className="cell auto"><a href="#" title={ t('common.classeurs.button.refus_title') } className="btn-refus"></a></div>
    )
}
ButtonRefus.contextTypes = { t: func }

const ButtonComment = ({}, {t}) => {
    return(
        <div className="cell auto"><a href="#" title={ t('common.classeurs.button.comment_title') } className="btn-comment"></a></div>
    )
}
ButtonComment.contextTypes = { t: func }