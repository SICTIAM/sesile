import React, { Component } from 'react'
import { object, array, func, number, oneOfType }from 'prop-types'
import { translate } from 'react-i18next'

class ClasseursButtonList extends Component {

    static contextTypes = {
        t: func
    }

    render () {

        const { classeurs, validClasseur, signClasseur, revertClasseur, removeClasseur, deleteClasseur, refuseClasseur } = this.props

        return (

            <div className="grid-x button-list align-middle text-center">
                {
                    classeurs && !classeurs.filter(classeur => !classeur.validable).length &&
                    <ButtonValid classeurs={ classeurs } valid={ validClasseur } />
                }

                {
                    classeurs && !classeurs.filter(classeur => !classeur.signable_and_last_validant).length &&
                    <ButtonSign classeurs={ classeurs } sign={ signClasseur }/>
                }

                {
                    classeurs && !classeurs.filter(classeur => !classeur.retractable).length &&
                    <ButtonRevert classeurs={ classeurs } revert={ revertClasseur }/>
                }

                {
                    classeurs && !classeurs.filter(classeur => !classeur.removable).length &&
                    <ButtonRemove classeurs={ classeurs } remove={ removeClasseur }/>
                }

                {
                    classeurs && !classeurs.filter(classeur => !classeur.deletable).length &&
                    <ButtonDelete classeurs={ classeurs } deleteClasseur={ deleteClasseur }/>
                }

                {
                    classeurs && !classeurs.filter(classeur => !classeur.refusable).length &&
                    <ButtonRefuse classeurs={ classeurs } refuseClasseur={ refuseClasseur } />
                }

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


const ButtonRefuse = ({classeurs, refuseClasseur}, {t}) => {
    return(
        <div className="cell auto"><a onClick={() => refuseClasseur(classeurs)} title={ t('common.classeurs.button.refus_title') } className="fa fa-times"></a></div>
    )
}
ButtonRefuse.contextTypes = { t: func }
ButtonRefuse.propTypes = {
    classeurs: array,
    refuseClasseur: func
}