import React, { Component } from 'react'
import { object, array, func, number, oneOfType }from 'prop-types'
import { translate } from 'react-i18next'

class ClasseursButtonList extends Component {

    static contextTypes = {
        t: func
    }

    render () {

        const { classeur, classeurs, validClasseur, revertClasseur, removeClasseur, deleteClasseur } = this.props

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
                    classeur && classeur.retractable &&
                    <ButtonRevert classeurs={ [classeur] } revert={ revertClasseur }/>
                }
                {
                    classeurs && !classeurs.filter(classeur => !classeur.retractable).length &&
                    <ButtonRevert classeurs={ classeurs } revert={ revertClasseur }/>
                }


                {
                    classeur && classeur.removable &&
                    <ButtonRemove classeurs={ [classeur] } remove={ removeClasseur }/>
                }
                {
                    classeurs && !classeurs.filter(classeur => !classeur.removable).length &&
                    <ButtonRemove classeurs={ classeurs } remove={ removeClasseur }/>
                }

                {
                    classeur && classeur.deletable &&
                    <ButtonDelete classeurs={ [classeur] } deleteClasseur={ deleteClasseur }/>
                }
                {
                    classeurs && !classeurs.filter(classeur => !classeur.deletable).length &&
                    <ButtonDelete classeurs={ classeurs } deleteClasseur={ deleteClasseur }/>
                }

                {
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
    validClasseur: func,
    revertClasseur: func,
    removeClasseur: func,
    deleteClasseur: func
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
        <div className="cell auto"><a title={ t('common.classeurs.button.sign_title') } className="btn-sign"></a></div>
    )
}
ButtonSign.contextTypes = { t: func }
ButtonSign.propTypes = {
    classeurs: array,
    sign: func
}

const ButtonRevert = ({classeurs, revert}, {t}) => {
    console.log(classeurs)
    return(
        <div className="cell auto"><a onClick={() => revert(classeurs)} title={ t('common.classeurs.button.revert_title') } className="btn-revert"></a></div>
    )
}
ButtonRevert.contextTypes = { t: func }
ButtonRevert.propTypes = {
    classeurs: array,
    revert: func
}

const ButtonRemove = ({classeurs, remove}, {t}) => {
    return(
        <div className="cell auto text-center"><a onClick={() => remove(classeurs)}  title={ t('common.classeurs.button.remove_title') }><span className="fi-minus btn-classeur-action"></span></a></div>
    )
}
ButtonRemove.contextTypes = { t: func }
ButtonRemove.propTypes = {
    classeurs: array,
    remove: func
}

const ButtonDelete = ({classeurs, deleteClasseur}, {t}) => {
    return(
        <div className="cell auto text-center"><a onClick={() => deleteClasseur(classeurs)}  title={ t('common.classeurs.button.delete_title') }><span className="fi-trash btn-classeur-action"></span></a></div>
    )
}
ButtonDelete.contextTypes = { t: func }
ButtonDelete.propTypes = {
    classeurs: array,
    deleteClasseur: func
}


const ButtonRefus = ({}, {t}) => {
    return(
        <div className="cell auto"><a href="#" title={ t('common.classeurs.button.refus_title') } className="btn-refus"></a></div>
    )
}
ButtonRefus.contextTypes = { t: func }
ButtonRefus.propTypes = {
    classeurs: array,
    refuse: func
}

const ButtonComment = ({}, {t}) => {
    return(
        <div className="cell auto"><a href="#" title={ t('common.classeurs.button.comment_title') } className="btn-comment"></a></div>
    )
}
ButtonComment.contextTypes = { t: func }
ButtonComment.propTypes = {
    classeurs: array,
    comment: func
}