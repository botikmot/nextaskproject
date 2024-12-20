import { VueRenderer } from '@tiptap/vue-3'
import tippy from 'tippy.js'
import axios from 'axios';
import MentionList from './MentionList.vue'

const fetchUserSuggestions = async (query) => {
    try {
      const response = await axios.get(`/users/search`, {
        params: { query },
      });
      // Assuming response data is an array of user names
      if(response.data){
        return response.data.map(user => ({
            id: user.id,
            name: user.name,
            email: user.email,
          }));
      }else{
        return []
      }
      
    } catch (error) {
      console.error('Error fetching user suggestions', error);
      return []; // Return an empty array in case of an error
    }
  };


export default {
  items: async ({ query }) => {
    const suggestions = await fetchUserSuggestions(query);
    return suggestions //.slice(0, 5); // Return up to 5 suggestions
  },

  render: () => {
    let component
    let popup

    return {
      onStart: props => {
        component = new VueRenderer(MentionList, {
          // using vue 2:
          // parent: this,
          // propsData: props,
          props,
          editor: props.editor,
        })

        if (!props.clientRect) {
          return
        }

        popup = tippy('body', {
          getReferenceClientRect: props.clientRect,
          appendTo: () => document.body,
          content: component.element,
          showOnCreate: true,
          interactive: true,
          trigger: 'manual',
          placement: 'bottom-start',
        })
      },

      onUpdate(props) {
        component.updateProps(props)

        if (!props.clientRect) {
          return
        }

        popup[0].setProps({
          getReferenceClientRect: props.clientRect,
        })
      },

      onKeyDown(props) {
        if (props.event.key === 'Escape') {
          popup[0].hide()

          return true
        }

        return component.ref?.onKeyDown(props)
      },

      onExit() {
        popup[0].destroy()
        component.destroy()
      },
    }
  },
}