import React, { Component } from 'react'
import { object, array, func, number, oneOfType }from 'prop-types'
import { translate } from 'react-i18next'

class ClasseursButtonList extends Component {

    static contextTypes = {
        t: func
    }

    render () {

        const { classeur, classeurs, validClasseur, signClasseur, revertClasseur, removeClasseur, deleteClasseur } = this.props

        return (

            <div className="grid-x button-list align-middle text-center">
                {
                    classeur && classeur.validable &&
                    <ButtonValid classeurs={ [classeur] } valid={ validClasseur } />
                }
                {
                    classeurs && !classeurs.filter(classeur => !classeur.validable).length &&
                    <ButtonValid classeurs={ classeurs } valid={ validClasseur } />
                }

                {
                    classeur && classeur.signable_and_last_validant &&
                    <ButtonSign classeurs={ [classeur] } sign={ signClasseur }/>
                }
                {
                    classeurs && !classeurs.filter(classeur => !classeur.signable_and_last_validant).length &&
                    <ButtonSign classeurs={ classeurs } sign={ signClasseur }/>
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
    deleteClasseur: func,
    signClasseur: func
}

export default translate(['sesile'])(ClasseursButtonList)

const ButtonValid = ({classeurs, valid}, {t}) => {
    return(
        <div className="cell auto"><a onClick={() => valid(classeurs)} title={ t('common.classeurs.button.valid_title') } className="fa fa-check"></a></div>
    )
}
ButtonValid.contextTypes = { t: func }
ButtonValid.propTypes = {
    classeurs: array,
    valid: func
}

const ButtonSign = ({classeurs, sign}, {t}) => {
    return(
        <div className="cell auto"><a onClick={() => sign(classeurs)} title={ t('common.classeurs.button.sign_title') } className="fa fa-pencil"></a></div>
    )
}
ButtonSign.contextTypes = { t: func }
ButtonSign.propTypes = {
    classeurs: array,
    sign: func
}

const ButtonRevert = ({classeurs, revert}, {t}) => {
    return(
        <div className="cell auto"><a onClick={() => revert(classeurs)} title={ t('common.classeurs.button.revert_title') } className="fa fa-repeat"></a></div>
    )
}
ButtonRevert.contextTypes = { t: func }
ButtonRevert.propTypes = {
    classeurs: array,
    revert: func
}

const ButtonRemove = ({classeurs, remove}, {t}) => {
    return(
        <div className="cell auto text-center"><a onClick={() => remove(classeurs)}  title={ t('common.classeurs.button.remove_title') }><span className="fa fa-times"></span></a></div>
    )
}
ButtonRemove.contextTypes = { t: func }
ButtonRemove.propTypes = {
    classeurs: array,
    remove: func
}

const ButtonDelete = ({classeurs, deleteClasseur}, {t}) => {
    return(
        <div className="cell auto text-center"><a onClick={() => deleteClasseur(classeurs)}  title={ t('common.classeurs.button.delete_title') }><span className="fa fa-times"></span></a></div>
    )
}
ButtonDelete.contextTypes = { t: func }
ButtonDelete.propTypes = {
    classeurs: array,
    deleteClasseur: func
}


const ButtonRefus = ({}, {t}) => {
    return(
        <div className="cell auto"><a href="#" title={ t('common.classeurs.button.refus_title') } className="fa fa-times"></a></div>
    )
}
ButtonRefus.contextTypes = { t: func }
ButtonRefus.propTypes = {
    classeurs: array,
    refuse: func
}