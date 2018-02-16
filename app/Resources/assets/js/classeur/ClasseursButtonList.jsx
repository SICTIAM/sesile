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
                <div className="cell auto">
                    {
                        classeurs && !classeurs.filter(classeur => !classeur.validable).length &&
                        <ButtonValid classeurs={ classeurs } valid={ validClasseur } />
                    }
                </div>
                <div className="cell auto">
                    {
                        classeurs && !classeurs.filter(classeur => !classeur.signable_and_last_validant).length &&
                        <ButtonSign classeurs={ classeurs } sign={ signClasseur }/>
                    }
                </div>
                <div className="cell auto">
                    {
                        classeurs && !classeurs.filter(classeur => !classeur.retractable).length &&
                        <ButtonRevert classeurs={ classeurs } revert={ revertClasseur }/>
                    }
                </div>
                <div className="cell auto">
                    {
                        classeurs && !classeurs.filter(classeur => !classeur.refusable).length &&
                        <ButtonRefuse classeurs={ classeurs } refuseClasseur={ refuseClasseur } />
                    }
                </div>
                <div className="cell auto">
                    {
                        classeurs && !classeurs.filter(classeur => !classeur.removable).length &&
                        <ButtonRemove classeurs={ classeurs } remove={ removeClasseur }/>
                    }
                </div>
                <div className="cell auto">
                    {
                        classeurs && !classeurs.filter(classeur => !classeur.deletable).length &&
                        <ButtonDelete classeurs={ classeurs } deleteClasseur={ deleteClasseur }/>
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
    signClasseur: func
}

export default translate(['sesile'])(ClasseursButtonList)

const ButtonValid = ({classeurs, valid}, {t}) => {
    return(
        <a onClick={() => valid(classeurs)} title={ t('common.classeurs.button.valid_title') } className="fa fa-check"></a>
    )
}
ButtonValid.contextTypes = { t: func }
ButtonValid.propTypes = {
    classeurs: array,
    valid: func
}

const ButtonSign = ({classeurs, sign}, {t}) => {
    return(
        <a onClick={() => sign(classeurs)} title={ t('common.classeurs.button.sign_title') } className="fa fa-pencil"></a>
    )
}
ButtonSign.contextTypes = { t: func }
ButtonSign.propTypes = {
    classeurs: array,
    sign: func
}

const ButtonRevert = ({classeurs, revert}, {t}) => {
    return(
        <a onClick={() => revert(classeurs)} title={ t('common.classeurs.button.revert_title') } className="fa fa-repeat"></a>
    )
}
ButtonRevert.contextTypes = { t: func }
ButtonRevert.propTypes = {
    classeurs: array,
    revert: func
}

const ButtonRemove = ({classeurs, remove}, {t}) => {
    return(
        <a onClick={() => remove(classeurs)} title={ t('common.classeurs.button.remove_title') } className="fa fa-times"></a>
    )
}
ButtonRemove.contextTypes = { t: func }
ButtonRemove.propTypes = {
    classeurs: array,
    remove: func
}

const ButtonDelete = ({classeurs, deleteClasseur}, {t}) => {
    return(
        <a onClick={() => deleteClasseur(classeurs)}  title={ t('common.classeurs.button.delete_title') } className="fa fa-trash"></a>
    )
}
ButtonDelete.contextTypes = { t: func }
ButtonDelete.propTypes = {
    classeurs: array,
    deleteClasseur: func
}


const ButtonRefuse = ({classeurs, refuseClasseur}, {t}) => {
    return(
        <a onClick={() => refuseClasseur(classeurs)} title={ t('common.classeurs.button.refus_title') } className="fa fa-minus-circle"></a>
    )
}
ButtonRefuse.contextTypes = { t: func }
ButtonRefuse.propTypes = {
    classeurs: array,
    refuseClasseur: func
}